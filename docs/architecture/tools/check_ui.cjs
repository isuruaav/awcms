// Execute all report render functions against a small DOM substitute. This is
// a deterministic functional check, not a browser screenshot/layout test.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
class Element {
  constructor(id) { this.id=id;this.innerHTML='';this.textContent='';this.value='';this.dataset={};this.states=new Set();this.classList={toggle:(key,on)=>{if(on)this.states.add(key);else this.states.delete(key);}}; }
  addEventListener() {}
  scrollIntoView() {}
}
const elements = new Map();
const get = id => { if(!elements.has(id))elements.set(id,new Element(id));return elements.get(id); };
const sections=['guide','files','routes','database','folders','packages','inventory'];
const panels=sections.map(name=>get('panel-'+name));
const buttons=sections.map(name=>{const e=new Element('tab-'+name);e.dataset.tab=name;return e;});
const context = vm.createContext({
  console,URLSearchParams,
  document:{getElementById:get,querySelector:()=>get('shell'),querySelectorAll:selector=>selector==='.panel'?panels:buttons,addEventListener(){}},
  history:{replaceState(){}},location:{hash:''},window:{addEventListener(){},print(){}},
});
const run = code => vm.runInContext(code,context,{timeout:30000});
run(fs.readFileSync(path.join(__dirname,'../report-ui.js'),'utf8'));
run("for(const section of ['files','routes','database','folders','packages','inventory'])activate(section)");
assert.match(get('route-list').innerHTML,/admin\/documents/);
assert.match(get('model-list').innerHTML,/school_leaders/);
assert.match(get('folder-list').innerHTML,/Local configuration snapshot/);
assert.match(get('package-list').innerHTML,/laravel\/framework/);
run("for (const file of DATA.files) showFile(file.path,false)");
run("showFile('app/Http/Controllers/PastCommandantController.php',false)");
assert.match(get('file-detail').innerHTML,/Unresolved literal view references/);
run("showFile('app/Livewire/Admin/Documents/DocumentUploads.php',false)");
assert.match(get('file-detail').innerHTML,/DocumentUploadManager/);
assert.match(get('file-detail').innerHTML,/admin\/documents/);
run("query='PageCss';activate('files')");
assert.match(get('file-list').innerHTML,/PageCss/);
run("query='school_leaders';activate('database')");
assert.match(get('model-list').innerHTML,/school_leaders/);
run("query='';activate('inventory');page=1;render()");
assert.match(get('inventory-list').innerHTML,/Previous/);
run("query='';showFile('.env',false)");
assert.doesNotMatch(get('file-detail').innerHTML,/APP_KEY=base64:/);
assert.ok(get('shell').states.has('wide'));
run("activate('guide')");
assert.equal(get('shell').states.has('wide'),false);
console.log(JSON.stringify({result:'passed',sections:7,file_detail_renders:run('DATA.files.length'),checks:['tabs','all file details','model relations','route handlers','source evidence','filters','pagination','missing fallback notice','full-width layout state']}));
