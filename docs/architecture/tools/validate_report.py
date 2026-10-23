"""Validate generated evidence, coverage, local links, and offline HTML."""
from __future__ import annotations
import csv
import json
import re
from html.parser import HTMLParser
from pathlib import Path
ROOT=Path(__file__).resolve().parents[3]
OUT=ROOT/'docs/architecture'
data=json.loads((OUT/'architecture-data.json').read_text(encoding='utf-8'))
files={f['path']:f for f in data['files']}
assert not data['parser_errors'],data['parser_errors']
assert len(files)==data['counts']['project_files']
assert len(data['routes'])==data['counts']['routes']
assert len(data['edges'])==data['counts']['edges']
assert len({x['path'] for x in data['inventory']})==len(data['inventory'])
assert all((ROOT/p).is_file() for p in files)
assert len(data['models'])==30
assert all(not r['resolution_error'] for r in data['routes'])
for route in data['routes']:
    if route['definition'] and route['definition']['uri'] is not None:
        assert route['definition']['uri'].strip('/')==route['uri'].strip('/'),route
    if (route['handler_file'] or '').startswith(('app/','routes/')):
        assert route['definition'],route
expected={
 'admin.documents.index':'app/Livewire/Admin/Documents/DocumentUploads.php',
 'admin.documents.create':'app/Livewire/Admin/Documents/DocumentUploads.php',
 'admin.documents.edit':'app/Livewire/Admin/Documents/DocumentUploads.php',
 'admin.users.index':'app/Livewire/Admin/Users/UserIndex.php',
}
routes={r['name']:r for r in data['routes'] if r['name']}
for name,target in expected.items(): assert routes[name]['handler_file']==target
checks=[
 ('app/Services/LeadershipProfileContent.php','scripts/compile-leadership-profile.mjs'),
 ('app/Http/Controllers/PublicPageController.php','app/Models/Page.php'),
 ('app/Http/Controllers/PublicPageController.php','app/Services/PageHtmlSanitizer.php'),
 ('app/Livewire/Admin/Documents/DocumentUploads.php','app/Services/DocumentUploadManager.php'),
 ('themes/school-of-signals/views/home.blade.php','public/themes/school-of-signals/assets/images/slsc_about.png'),
]
pairs={(e['source'],e['target']) for e in data['edges']}
for pair in checks: assert pair in pairs,pair
assert files['app/Http/Controllers/PastCommandantController.php']['unresolved_views'][0]['name']=='public.history.past-commandants'
assert not files['.env']['classes'] and not files['.env']['methods']
allowed={'app.env','app.debug','app.url','awcms.active_theme','database.default','cache.default','session.driver','queue.default','filesystems.default','images.default'}
assert set(data['configuration'])==allowed
assert not any(x['path'].startswith('public/storage/') for x in data['inventory'])
assert any(x['path']=='public/storage' and x['kind']=='directory link' for x in data['inventory'])

class Structure(HTMLParser):
    def __init__(self): super().__init__();self.ids=set();self.local=[];self.anchors=[];self.scripts=0;self.remote=[]
    def handle_starttag(self,tag,attrs):
        attrs=dict(attrs)
        if 'id' in attrs:
            assert attrs['id'] not in self.ids,attrs['id']
            self.ids.add(attrs['id'])
        if tag=='script':self.scripts+=1
        for key in ['src','href']:
            if key not in attrs:continue
            value=attrs[key]
            if value.startswith(('http:','https:')) and key=='src':self.remote.append(value)
            if value.startswith('#guide-'):self.anchors.append(value[1:])
            elif value and not value.startswith(('#','http:','https:','data:')):self.local.append(value)

markup=(OUT/'index.html').read_text(encoding='utf-8')
parser=Structure();parser.feed(markup)
assert parser.scripts==1
assert not parser.remote
assert not re.search(r'@import\s+[\'"]https?://',markup)
assert all(a in parser.ids for a in parser.anchors)
assert all((OUT/href.split('#')[0]).exists() for href in parser.local),parser.local
assert not re.search(r'\{\{(?:project_files|routes|models|edges|inventory_entries)\}\}',markup)
assert 'charset="utf-8"' in markup
assert 'data:font/woff2;base64,' in markup
assert 'APP_KEY=base64:' not in markup
assert '\ufffd' not in (OUT/'ARCHITECTURE-REPORT.si.md').read_text(encoding='utf-8')
md=(OUT/'ARCHITECTURE-REPORT.si.md').read_text(encoding='utf-8')
anchors=set(re.findall(r'<a id="([^"]+)"',md))
assert len(anchors)==len(files)
assert all(anchor in anchors for anchor in re.findall(r'\]\(#(file-[^)]+)\)',md))
for name,count in [('all-files.csv',len(data['inventory'])),('dependencies.csv',len(data['edges'])),('routes.csv',len(data['routes']))]:
    with (OUT/name).open(encoding='utf-8-sig',newline='') as stream: assert len(list(csv.DictReader(stream)))==count
summary={'result':'passed','project_files':len(files),'routes':len(data['routes']),'models':len(data['models']),'edges':len(data['edges']),'inventory_entries':len(data['inventory']),'known_missing_fallback_view':'public.history.past-commandants','browser_visual_preview':'unavailable in current session; functional render checks run separately','checks':['source paths','route source/URI alignment','Livewire resolution','critical feature chains','storage link deduplication','safe config whitelist','HTML anchors/downloads','offline font/assets','Markdown links','CSV row counts']}
(OUT/'validation.json').write_text(json.dumps(summary,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(summary,ensure_ascii=True))
