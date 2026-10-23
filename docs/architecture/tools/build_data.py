"""Build a static dependency index plus a metadata-only filesystem inventory.

Run extract_php.php and inspect_runtime.php first. Never read .env contents,
database rows, uploaded documents, logs, Git objects, or dependency source trees.
"""
from __future__ import annotations
import csv
import hashlib
import json
import os
import re
from collections import Counter, defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
OUT = ROOT / 'docs/architecture'
PHP = json.loads((OUT / 'php-source.json').read_text(encoding='utf-8-sig'))
RUNTIME = json.loads((OUT / 'runtime-metadata.json').read_text(encoding='utf-8-sig'))
ROUTES = json.loads((OUT / 'routes-runtime.json').read_text(encoding='utf-8-sig'))
SOURCE_EXCLUDES = ('vendor/', 'node_modules/', '.git/', 'storage/', 'public/build/', 'public/themes/', 'public/storage/', 'deploy/updates/', 'docs/architecture/')
TEXT_EXTENSIONS = {'.php', '.js', '.mjs', '.py', '.css', '.html', '.json', '.md', '.xml', '.neon', '.yml', '.yaml', '.txt', '.svg', '.conf'}

def category(path: str) -> str:
    if path.startswith('vendor/'): return 'PHP dependency'
    if path.startswith('node_modules/'): return 'Node dependency'
    if path.startswith('.git/'): return 'Git metadata'
    if path.startswith(('storage/app/', 'database/database.sqlite')): return 'Application data'
    if path.startswith(('storage/', 'bootstrap/cache/', 'public/build/', 'public/themes/')): return 'Generated/runtime'
    if path.startswith('deploy/updates/'): return 'Deployment package'
    if path.startswith(('.cursor/', '.vscode/', '.github/')): return 'Development/automation'
    if path == '.env': return 'Private environment (contents excluded)'
    return 'Project source/configuration'

inventory, directories = [], []
for base, dirs, names in os.walk(ROOT, followlinks=False):
    relative_base = Path(base).relative_to(ROOT).as_posix()
    if relative_base == 'docs/architecture' or relative_base.startswith('docs/architecture/'):
        dirs[:] = []
        continue
    directories.append(relative_base)
    for name in list(dirs):
        p = Path(base) / name
        if p.is_symlink() or p.is_junction():
            relative = p.relative_to(ROOT).as_posix()
            inventory.append({'path': relative, 'kind': 'directory link', 'category': 'Filesystem alias', 'bytes': 0, 'target': str(p.resolve())})
            dirs.remove(name)
    for name in names:
        p = Path(base) / name
        relative = p.relative_to(ROOT).as_posix()
        try:
            stat = p.lstat()
            inventory.append({'path': relative, 'kind': 'file link' if p.is_symlink() else 'file', 'category': category(relative), 'bytes': stat.st_size, 'target': str(p.resolve()) if p.is_symlink() else ''})
        except OSError:
            continue
inventory.sort(key=lambda x: x['path'])
file_paths = {x['path'] for x in inventory}
known_paths = file_paths | set(directories)
metadata = {x['path']: x for x in inventory}
source_paths = sorted(p for p in file_paths if metadata[p]['kind'] in ['file', 'file link'] and not p.startswith(SOURCE_EXCLUDES) and not p.startswith('bootstrap/cache/') and p != 'database/database.sqlite')
records = {p: {'path': p, 'category': category(p), 'bytes': metadata[p]['bytes'], 'classes': [], 'methods': [], 'external_imports': [], 'views': [], 'unresolved_views': [], 'config_keys': [], 'permissions': [], 'wire_actions': [], 'tests': [], 'outgoing': [], 'incoming': []} for p in source_paths}
class_files = {}
for rec in PHP:
    for cls in rec['classes']:
        if cls['name'] != '(anonymous)': class_files[cls['name']] = rec['path']
php_by_path = {r['path']: r for r in PHP}
edges = {}

def edge(source, target, kind, line=1, evidence='', confidence='static explicit'):
    if source == target or source not in records or target not in known_paths:
        return
    key = (source, target, kind, int(line or 1))
    edges[key] = {'source': source, 'target': target, 'kind': kind, 'line': int(line or 1), 'evidence': str(evidence)[:180], 'confidence': confidence}

view_files = {}
for path in source_paths:
    if path.startswith('resources/views/') and path.endswith('.blade.php'):
        view_files[path[len('resources/views/'):-len('.blade.php')].replace('/', '.')] = path
    match = re.match(r'themes/([^/]+)/views/(.+)\.blade\.php$', path)
    if match:
        view_files['theme-' + match[1] + '::' + match[2].replace('/', '.')] = path

def resolve_view(name):
    if name in view_files: return view_files[name]
    if '::' in name:
        namespace, suffix = name.split('::', 1)
        p = f'resources/views/{namespace}/{suffix.replace(".", "/")}.blade.php'
        if p in known_paths: return p
    return None

def livewire_class(name):
    if name in class_files: return class_files[name]
    classname = 'App\\Livewire\\' + '\\'.join(''.join(bit.capitalize() for bit in part.split('-')) for part in name.split('.'))
    if classname in class_files: return class_files[classname]
    for prefix in ['resources/views/components/', 'resources/views/livewire/']:
        raw = name.replace('.', '/')
        for candidate in [prefix + raw + '.blade.php', prefix + str(Path(raw).parent).replace('\\', '/') + '/⚡' + Path(raw).name + '.blade.php']:
            if candidate in known_paths: return candidate
    return None

route_definitions = {}
all_route_declarations = []
for path in ['routes/web.php', 'routes/settings.php']:
    for declaration in php_by_path[path]['route_declarations']:
        all_route_declarations.append({'path': path, **declaration})
        if declaration['name']: route_definitions[declaration['name']] = {'path': path, **declaration}

runtime_by_name = {r['name']: r for r in RUNTIME['routes'] if r['name']}
runtime_by_signature = {('|'.join(r['methods']), r['uri']): r for r in RUNTIME['routes']}
for r in ROUTES:
    details = runtime_by_name.get(r['name'], runtime_by_signature.get((r['method'], r['uri']), {}))
    definition = route_definitions.get(r['name'], {})
    if not definition:
        for declaration in all_route_declarations:
            if declaration['name'] is None and declaration['uri'] is not None and declaration['uri'].strip('/') == r['uri'].strip('/'):
                definition = declaration
                break
            if declaration['method'] == 'fallback' and declaration['arguments'] and isinstance(declaration['arguments'][0], dict) and declaration['arguments'][0].get('class') == r['action'].split('@')[0]:
                definition = declaration
                break
    r['definition'] = definition
    r['component'] = details.get('component')
    r['resolved_component'] = details.get('resolved_component')
    r['handler_file'] = details.get('component_source') or details.get('source')
    r['handler_line'] = details.get('line')
    r['resolution_error'] = details.get('error')
    r['constraints'] = details.get('constraints', {})
    r['defaults'] = details.get('defaults', {})
    target = r['handler_file']
    if target and definition: edge(definition['path'], target, 'registered route handler', definition['line'], r['name'] or r['uri'], 'runtime metadata + source')

for rec in PHP:
    path = rec['path']
    if path not in records: continue
    data = records[path]
    data['classes'] = rec['classes']
    data['methods'] = [method for cls in rec['classes'] for method in cls['methods']]
    for imp in rec['imports']:
        if imp['name'] in class_files: edge(path, class_files[imp['name']], 'PHP import (declaration)', imp['line'], imp['name'])
        else: data['external_imports'].append(imp['name'])
    for ref in rec['references']:
        if ref['name'] in class_files: edge(path, class_files[ref['name']], 'PHP class/type reference', ref['line'], ref['name'])
    for call in rec['calls']:
        name, args, line = call['name'].split('\\')[-1], call['arguments'], call['line']
        first = args[0] if args else None
        if isinstance(first, str):
            if name in ['view', 'layout', 'extends'] or (name == 'make' and call['receiver'].endswith('\\View')):
                target = resolve_view(first)
                if target:
                    edge(path, target, 'renders/layout view', line, first)
                    data['views'].append(first)
                elif path.startswith('app/'):
                    data['unresolved_views'].append({'name': first, 'line': line, 'note': 'No matching literal project view; package/dynamic resolution may apply.'})
            if name == 'resolve':
                target = f'themes/school-of-signals/views/{first.replace(".", "/")}.blade.php'
                if target in known_paths: edge(path, target, 'active-theme view candidate', line, first, 'conditional: active theme + existing view')
            if name == 'config':
                data['config_keys'].append(first)
                target = 'config/' + first.split('.')[0] + '.php'
                edge(path, target, 'configuration read', line, first)
            if name in ['authorize', 'can', 'allows', 'hasPermissionTo']:
                data['permissions'].append({'name': first, 'line': line})
            if name in ['route', 'routeIs']:
                definition = route_definitions.get(first)
                if definition: edge(path, definition['path'], 'named route URL/reference', line, first)
            prefixes = {'base_path': '', 'resource_path': 'resources/', 'public_path': 'public/', 'storage_path': 'storage/', 'database_path': 'database/'}
            if name in prefixes:
                edge(path, prefixes[name] + first.replace('\\', '/'), 'filesystem path reference', line, name + '(' + first + ')')
            if name == 'env' and path.startswith('config/'):
                edge(path, '.env', 'environment variable lookup', line, first, 'configuration binding; values excluded')
        if path.startswith('tests/'):
            for item in args:
                if isinstance(item, str) and name in ['test', 'it']: data['tests'].append({'description': item, 'line': line})

texts = {}
for path in source_paths:
    p = ROOT/path
    if path == '.env' or p.name in ['composer.lock', 'package-lock.json'] or p.suffix.lower() not in TEXT_EXTENSIONS and not p.name.startswith('.') and p.name != 'artisan': continue
    try: texts[path] = p.read_text(encoding='utf-8-sig', errors='replace')
    except OSError: continue

def add_view_reference(path, name, line, kind):
    target = resolve_view(name)
    if target: edge(path, target, kind, line, name)

for path, text in texts.items():
    data = records[path]
    for name in re.finditer(r'\\?(App\\[A-Za-z_][A-Za-z0-9_\\]+)', text):
        target = class_files.get(name[1])
        if target: edge(path, target, 'fully qualified class reference', text.count('\n', 0, name.start())+1, name[1])
    if path.endswith('.blade.php'):
        for match in re.finditer(r'@(extends|include|includeIf|component)\s*\(\s*[\'"]([^\'"]+)[\'"]', text):
            add_view_reference(path, match[2], text.count('\n', 0, match.start())+1, 'Blade '+match[1])
        for match in re.finditer(r'\b(?:view|layout)\s*\(\s*[\'"]([^\'"]+)[\'"]', text):
            add_view_reference(path, match[1], text.count('\n', 0, match.start())+1, 'Blade PHP view reference')
        for match in re.finditer(r'<x-([A-Za-z0-9_.:\-]+)', text):
            target = 'resources/views/components/' + match[1].replace('.', '/') + '.blade.php'
            if '::' in match[1]: target = 'resources/views/' + match[1].replace('::', '/').replace('.', '/') + '.blade.php'
            edge(path, target, 'Blade component', text.count('\n', 0, match.start())+1, match[1])
        for match in re.finditer(r'<livewire:([A-Za-z0-9_.:\-]+)', text):
            target = livewire_class(match[1])
            if target: edge(path, target, 'embedded Livewire component', text.count('\n', 0, match.start())+1, match[1])
        for match in re.finditer(r'wire:(?:click|submit|change|keyup|keydown)(?:[.\w-]*)\s*=\s*"([^"\n]+)"', text):
            data['wire_actions'].append({'action': match[1][:140], 'line': text.count('\n', 0, match.start())+1})
    for match in re.finditer(r'\broute\s*\(\s*[\'"]([^\'"]+)[\'"]', text):
        definition = route_definitions.get(match[1])
        if definition: edge(path, definition['path'], 'named route URL/reference', text.count('\n', 0, match.start())+1, match[1])
    for match in re.finditer(r'\bconfig\s*\(\s*[\'"]([^\'"]+)[\'"]', text):
        name = match[1]
        data['config_keys'].append(name)
        edge(path, 'config/'+name.split('.')[0]+'.php', 'configuration read', text.count('\n',0,match.start())+1, name)
    for match in re.finditer(r'\basset\s*\(\s*[\'"]([^\'"]+)[\'"]', text):
        edge(path, 'public/'+match[1], 'public asset URL', text.count('\n',0,match.start())+1, match[1])
    for match in re.finditer(r'[\'"]((?:app|resources|themes|scripts|routes|config|public|database|bootstrap)/[^\'"\n]+)[\'"]', text):
        target = match[1]
        if target in known_paths: edge(path, target, 'literal project path', text.count('\n',0,match.start())+1, target)
    if path.endswith(('.js', '.mjs', '.css')):
        for match in re.finditer(r'(?:from\s*|import\s*|@import\s*|@source\s*|url\(\s*)[\'"]([^\'"]+)[\'"]', text):
            value = match[1]
            if value.startswith('.'):
                target = os.path.relpath((ROOT/path).parent/value, ROOT).replace('\\','/')
                edge(path, target, 'JS/CSS import or source', text.count('\n',0,match.start())+1, value)
            elif not value.startswith(('http', '/', '#')):
                edge(path, 'package.json', 'Node package dependency', text.count('\n',0,match.start())+1, value)

# Relationships established by explicit project conventions or runtime metadata.
for source, target, kind in [
    ('public/index.php','bootstrap/app.php','HTTP bootstrap'), ('public/index.php','vendor/autoload.php','Composer autoloader'),
    ('public/.htaccess','public/index.php','Apache rewrite target'), ('artisan','bootstrap/app.php','console bootstrap'),
    ('artisan','vendor/autoload.php','Composer autoloader'), ('bootstrap/app.php','bootstrap/providers.php','provider discovery convention'),
    ('bootstrap/app.php','routes/web.php','web route registration'), ('bootstrap/app.php','routes/console.php','console route registration'),
    ('routes/web.php','routes/settings.php','required route file'), ('composer.json','composer.lock','locked PHP dependency versions'),
    ('package.json','package-lock.json','locked Node dependency versions'), ('package.json','vite.config.js','frontend build command'),
    ('vite.config.js','public/build','compiled frontend output'), ('config/filesystems.php','public/storage','configured public storage link'),
    ('config/filesystems.php','storage/app/public','public disk root'), ('config/filesystems.php','storage/app/private','private disk root'),
    ('phpunit.xml','tests','test discovery'), ('tests/Pest.php','tests/TestCase.php','Pest base test class'),
    ('phpstan.neon','storage/framework/cache/phpstan','PHPStan temporary cache'),
]: edge(source,target,kind,1,'project bootstrap/configuration convention','explicit convention')
for cls in ['App\\Providers\\AppServiceProvider','App\\Providers\\ThemeServiceProvider','App\\Providers\\FortifyServiceProvider']:
    edge('bootstrap/providers.php',class_files[cls],'service provider registration',1,cls)
for path in source_paths:
    if path.startswith('tests/Feature/'):
        edge(path,'tests/Pest.php','Pest Feature test binding',1,'Tests\\TestCase + RefreshDatabase','explicit test convention')
    if path.startswith('themes/school-of-signals/assets/'):
        target = 'public/' + path
        edge('app/Services/ThemeAssetPublisher.php',path,'theme asset source',1,path,'directory publication convention')
        edge(path,target,'published asset copy',1,'source -> public/themes','directory publication convention')
        if target in known_paths:
            metadata[target]['matches_source_sha256'] = hashlib.sha256((ROOT/path).read_bytes()).digest() == hashlib.sha256((ROOT/target).read_bytes()).digest()
    if path.startswith('themes/') and path.endswith('/theme.json'):
        manifest=json.loads(texts[path]); root=path.rsplit('/',1)[0]
        for name in manifest.get('views',{}).values(): edge(path,root+'/views/'+name.replace('.','/')+'.blade.php','theme manifest view',1,name)
        for value in manifest.get('assets',{}).values(): edge(path,root+'/'+value,'theme manifest asset',1,value)
    if path.startswith('lang/') or '/lang/' in path:
        edge('app/Providers/ThemeServiceProvider.php',path,'translation resource discovery',1,path,'directory convention') if path.startswith('themes/') else edge('config/app.php',path,'application translation discovery',1,path,'directory convention')

table_migrations=defaultdict(list)
schema=[]
for rec in PHP:
    if not rec['path'].startswith('database/migrations/'): continue
    for table in rec['schema']:
        table={**table,'file':rec['path']}; schema.append(table)
        if isinstance(table['table'],str) and table['operation'] in ['create','table']:
            table_migrations[table['table']].append(table)
models=[]
for model in RUNTIME['models']:
    path=class_files[model['class']]
    model={**model,'file':path,'relations':php_by_path[path]['relations'],'migrations':table_migrations[model['table']]}
    models.append(model)
    records[path]['table']=model['table']
    for migration in model['migrations']: edge(path,migration['file'],'model table schema',php_by_path[path]['classes'][0]['line'],model['table']+'; migration line '+str(migration['line']),'runtime table name + migration AST')

edge_list=sorted(edges.values(),key=lambda x:(x['source'],x['target'],x['kind'],x['line']))
for e in edge_list:
    records[e['source']]['outgoing'].append(e)
    if e['target'] in records: records[e['target']]['incoming'].append(e)
for record in records.values():
    record['config_keys']=sorted(set(record['config_keys']))
    record['views']=sorted(set(record['views']))
    record['external_imports']=sorted(set(record['external_imports']))
    record['routes']=[{'name':r['name'],'uri':r['uri'],'method':r['method']} for r in ROUTES if r['handler_file']==record['path']]

lock=json.loads((ROOT/'composer.lock').read_text(encoding='utf-8'))
packages=[{'name':x['name'],'version':x['version'],'development':dev,'requires':x.get('require',{}),'description':x.get('description','')} for dev,key in [(False,'packages'),(True,'packages-dev')] for x in lock[key]]
folder_rows=[]
for directory in sorted(directories):
    prefix='' if directory=='.' else directory+'/'
    rows=[x for x in inventory if x['path'].startswith(prefix)]
    folder_rows.append({'path':directory,'files':sum(x['kind']=='file' for x in rows),'bytes':sum(x['bytes'] for x in rows)})
data={'snapshot':'2026-10-22 (Asia/Colombo)','root':str(ROOT),'files':list(records.values()),'edges':edge_list,'routes':ROUTES,'models':models,'schema':schema,'packages':packages,'node_manifest':json.loads((ROOT/'package.json').read_text()),'configuration':RUNTIME['configuration'],'inventory':inventory,'folders':folder_rows,'parser_errors':[{'path':r['path'],'error':r['error']} for r in PHP if r['error']],'counts':{'project_files':len(records),'inventory_entries':len(inventory),'directories':len(directories),'routes':len(ROUTES),'models':len(models),'edges':len(edge_list),'parsed_php_files':len(PHP)}}
(OUT/'architecture-data.json').write_text(json.dumps(data,ensure_ascii=False,separators=(',',':')),encoding='utf-8')
def csv_write(name,rows,fields):
    with (OUT/name).open('w',encoding='utf-8-sig',newline='') as stream:
        writer=csv.DictWriter(stream,fieldnames=fields,extrasaction='ignore'); writer.writeheader(); writer.writerows(rows)
csv_write('all-files.csv',inventory,['path','kind','category','bytes','target','matches_source_sha256'])
csv_write('dependencies.csv',edge_list,['source','target','kind','line','evidence','confidence'])
csv_write('folders.csv',folder_rows,['path','files','bytes'])
csv_write('routes.csv',[{**r,'middleware':' | '.join(r['middleware']),'definition_file':r['definition'].get('path',''),'definition_line':r['definition'].get('line','')} for r in ROUTES],['method','uri','name','action','resolved_component','handler_file','definition_file','definition_line','middleware'])
print(json.dumps({'counts':data['counts'],'parser_errors':data['parser_errors'],'route_source_matches':sum(bool(r['definition']) for r in ROUTES),'livewire_routes':sum(bool(r['resolved_component']) for r in ROUTES),'inventory_categories':dict(Counter(x['category'] for x in inventory))},ensure_ascii=True))
