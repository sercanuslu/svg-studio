<?php
session_start();
$valid_username='user'; $valid_password='password';
if(!isset($_SESSION['loggedin'])){
  if($_SERVER['REQUEST_METHOD']==='POST' &&
     $_POST['username']===$valid_username &&
     $_POST['password']===$valid_password){
        $_SESSION['loggedin']=true; header('Location:'.$_SERVER['PHP_SELF']); exit;
  }
  $e=@$_POST['username']?'Hatalı bilgiler':'';
  ?><!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Giriş</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
  <body class="bg-dark text-light d-flex justify-content-center align-items-center vh-100">
  <form method="post" class="bg-secondary p-5 rounded shadow">
     <h2 class="mb-4">SVG Girişi</h2>
     <input name="username" class="form-control mb-3" placeholder="Kullanıcı adı" required>
     <input name="password" type="password" class="form-control mb-3" placeholder="Şifre" required>
     <button class="btn btn-primary w-100">Giriş Yap</button>
     <?php if($e) echo"<div class='text-danger mt-2'>$e</div>";?>
  </form></body></html><?php exit;
}

/* ---------- SVG indeksle ---------- */
function list_svg($dir):array{
  $rii=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
  $out=[];
  foreach($rii as $f){
     if(!$f->isDir() && strtolower($f->getExtension())==='svg'){
        $rel=str_replace($dir.DIRECTORY_SEPARATOR,'',$f->getPathname());
        $name=pathinfo($rel,PATHINFO_FILENAME);
        $tags=preg_split('/[\W_]+/',$name);
        $out[]=['p'=>$rel,'t'=>$tags];
     }
  } return $out;
}
$svg_dir=__DIR__.'/svgs';
$idx=list_svg($svg_dir);
file_put_contents(__DIR__.'/svg_index.json',json_encode($idx,JSON_UNESCAPED_UNICODE));
$folders=array_unique(array_map(fn($o)=>explode('/',$o['p'])[0],$idx));
?>
<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>SVG Studio</title>
<link  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/canvg/3.0.10/umd.min.js"></script>
<style>
body{overflow-x:hidden}
.svg-box{position:relative;height:240px;display:flex;flex-direction:column;align-items:center;justify-content:center}
.svg-box svg{width:100px;height:100px}
.svg-name{font-size:.85rem;margin-top:8px;word-break:break-all}
.svg-folder{font-size:.75rem;margin-top:2px}
.favorite-btn{position:absolute;top:6px;right:8px;font-size:1.25rem;cursor:pointer;color:#ffc107}
.favorite-btn.not-fav{color:#adb5bd}
.select-box{position:absolute;top:6px;left:8px}
.checker{background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAAG0lEQVQoU2NkQAKMgmEYBaNgFIYEMFoYGhoYAKyRAeM4BHGRAAAAAElFTkSuQmCC") repeat}
.drag-overlay{pointer-events:none;position:fixed;inset:0;background:#0008;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;opacity:0;transition:.2s;z-index:2000}
.drag-overlay.show{pointer-events:all;opacity:1}
</style></head><body class="bg-dark text-light">
<div class="container py-3">


<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
  <h1 class="h3 m-0 me-auto">SVG Studio</h1>
  <button id="zipBtn" class="btn btn-success btn-sm d-none"><i class="fa-solid fa-file-zipper me-1"></i>
    ZIP indir (<span id="zipCnt">0</span>)</button>
  <button id="allBtn" class="btn btn-outline-light btn-sm">Tümü</button>
  <button id="favBtn" class="btn btn-warning btn-sm">
    <i class="fa-solid fa-star me-1"></i>Favoriler <span id="favCnt" class="badge bg-dark ms-1">0</span>
  </button>
</div>


<div class="mb-3 text-center">
  <button class="btn btn-sm btn-warning m-1 folder-link" data-folder="">📂 Tüm Klasörler</button>
  <?php foreach($folders as $f): ?>
     <button class="btn btn-sm btn-outline-light m-1 folder-link" data-folder="<?=htmlspecialchars($f)?>">📁 <?=$f?></button>
  <?php endforeach;?>
</div>


<input id="search" class="form-control form-control-lg mb-4" placeholder="Dosya adı / etiket...">


<div id="results" class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4 g-4"></div>
<ul id="pages" class="pagination justify-content-center mt-4"></ul>
</div>

<div id="dragOv" class="drag-overlay">SVG’i Bırak…</div>


<div class="modal fade" id="editor" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content bg-dark text-light">
  <div class="modal-header"><h5 class="modal-title">SVG Düzenleyici</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="d-flex flex-wrap gap-4">
      <div class="flex-grow-1 p-3 border rounded text-center">
         <div id="prevWrap"><!-- preview --></div>
      </div>
      <div style="min-width:260px">
        <label class="form-label mt-2">Fill</label>
        <input type="color" id="fillPick" class="form-control form-control-color mb-2">
        <label class="form-label">Stroke</label>
        <input type="color" id="strokePick" class="form-control form-control-color mb-2">
        <label class="form-label">Genişlik (px)</label>
        <input type="number" id="wInp" class="form-control mb-2" min="1">
        <label class="form-label">Yükseklik (px)</label>
        <input type="number" id="hInp" class="form-control mb-3" min="1">
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="bgChk"><label class="form-check-label">Kareli zemin</label>
        </div>
        <button id="copyBtn" class="btn btn-outline-info w-100 mb-2"><i class="fa-solid fa-copy me-1"></i>Kopyala</button>
        <a id="svgDl" class="btn btn-success w-100 mb-2" download><i class="fa-solid fa-download me-1"></i>SVG indir</a>
        <button id="pngDl" class="btn btn-primary w-100"><i class="fa-solid fa-image me-1"></i>PNG indir</button>
      </div>
    </div>
  </div>
</div></div></div>

<script>

let data=[], filt=[], folder='', showFav=false, pg=1, per=32;
let sel=new Set();
const favKey='favs';


const R=document.getElementById('results'), P=document.getElementById('pages'),
favCnt=document.getElementById('favCnt'), zipCnt=document.getElementById('zipCnt'),
zipBtn=document.getElementById('zipBtn');


const getFav=()=>JSON.parse(localStorage.getItem(favKey)||'[]');
const setFav=a=>{localStorage.setItem(favKey,JSON.stringify(a)); favCnt.textContent=a.length;};


fetch('svg_index.json').then(r=>r.json()).then(j=>{data=j; setFav(getFav()); filter();});


function filter(){
  const q=document.getElementById('search').value.toLowerCase(), favs=getFav();
  filt=data.filter(o=>{
     const fOk=!folder||o.p.startsWith(folder+'/');
     const qOk=!q||(o.p.toLowerCase().includes(q)||o.t.some(t=>t.toLowerCase().includes(q)));
     const fv=!showFav||favs.includes(o.p);
     return fOk&&qOk&&fv;
  }); pg=1; render();
}
function render(){
  R.innerHTML=''; P.innerHTML='';
  zipCnt.textContent=sel.size; zipBtn.classList.toggle('d-none',!sel.size);
  const favs=getFav(), st=(pg-1)*per;
  filt.slice(st,st+per).forEach(o=>{
    fetch('svgs/'+o.p).then(r=>r.text()).then(txt=>{
      const name=o.p.split('/').pop(), fold=o.p.split('/')[0];
      const isFav=favs.includes(o.p), chk=sel.has(o.p);
      const col=document.createElement('div'); col.className='col';
      col.innerHTML=`
      <div class="bg-white p-3 rounded text-dark text-center shadow svg-box">
        <input type="checkbox" class="form-check-input select-box" data-p="${o.p}" ${chk?'checked':''}>
        <span class="favorite-btn ${isFav?'':'not-fav'}" data-p="${o.p}"><i class="fa-solid fa-star"></i></span>
        <span class="edit-btn position-absolute bottom-0 end-0 me-2 mb-2 text-primary" style="cursor:pointer" data-p="${o.p}">
           <i class="fa-solid fa-pen"></i></span>
        ${txt}
        <div class="svg-name">${name}</div>
        <div class="svg-folder text-info">${fold}</div>
      </div>`; R.appendChild(col);
    });
  });
  const tot=Math.ceil(filt.length/per);
  if(tot>1){
     const add=(n,l,act)=>{const li=document.createElement('li');
       li.className='page-item'+(act?' active':''); li.innerHTML=`<a class="page-link" href="#">${l||n}</a>`;
       li.onclick=e=>{e.preventDefault(); pg=n; render();}; P.appendChild(li);}
     if(pg>1) add(pg-1,'‹');
     for(let i=1;i<=tot;i++){
       if(i==1||i==tot||Math.abs(i-pg)<=2) add(i,null,i==pg);
       else if((i==2&&pg>4)||(i==tot-1&&pg<tot-3))
          P.insertAdjacentHTML('beforeend','<li class="page-item disabled"><span class="page-link">…</span></li>');
     }
     if(pg<tot) add(pg+1,'›');
  }
}


R.addEventListener('click',e=>{
  const fav=e.target.closest('.favorite-btn'), chk=e.target.closest('.select-box'), ed=e.target.closest('.edit-btn');
  if(fav){ let favs=getFav(),p=fav.dataset.p,idx=favs.indexOf(p);
    idx>-1?favs.splice(idx,1):favs.push(p); setFav(favs); filter(); return;}
  if(chk){ chk.checked?sel.add(chk.dataset.p):sel.delete(chk.dataset.p);
    zipCnt.textContent=sel.size; zipBtn.classList.toggle('d-none',!sel.size); return;}
  if(ed) openEditor(ed.dataset.p);
});
document.getElementById('search').oninput=filter;
document.querySelectorAll('.folder-link').forEach(b=>b.onclick=()=>{folder=b.dataset.folder; filter();});
document.getElementById('favBtn').onclick=()=>{showFav=!showFav; filter();};
document.getElementById('allBtn').onclick=()=>{showFav=false; folder=''; filter();};


zipBtn.onclick=async()=>{
  const zip=new JSZip(); await Promise.all([...sel].map(async p=>{
     zip.file(p.split('/').pop(),await fetch('svgs/'+p).then(r=>r.blob()));
  })); zip.generateAsync({type:'blob'}).then(b=>saveAs(b,'icons.zip'));
};


const E=new bootstrap.Modal('#editor'), prevWrap=document.getElementById('prevWrap');
const fillPick=document.getElementById('fillPick'), strokePick=document.getElementById('strokePick'),
wInp=document.getElementById('wInp'), hInp=document.getElementById('hInp');
let curTxt='', curPath='';

async function openEditor(path){
  curTxt=await fetch('svgs/'+path).then(r=>r.text()); curPath=path;
  loadPrev(curTxt); E.show();
}
function loadPrev(txt){
  prevWrap.innerHTML=txt; prevWrap.classList.toggle('checker',document.getElementById('bgChk').checked);
  const svg=prevWrap.querySelector('svg'); if(!svg) return;
  fillPick.value=getFirstColor(svg,'fill')||'#000000';
  strokePick.value=getFirstColor(svg,'stroke')||'#000000';
  wInp.value=parseInt(svg.getAttribute('width')||'');
  hInp.value=parseInt(svg.getAttribute('height')||'');
  updateDl();
}
function getFirstColor(svg,attr){
  const el=[...svg.querySelectorAll('*')].find(e=>e.hasAttribute(attr)&&e.getAttribute(attr)!=='none');
  const c=el?.getAttribute(attr); return c?toHex(c):null;
}
function toHex(c){ if(c.startsWith('#')) return c;
  const m=c.match(/\d+/g); if(!m) return '#000000';
  return '#'+m.slice(0,3).map(x=>('0'+(+x).toString(16)).slice(-2)).join('');
}
function applyEdits(){
  const doc=new DOMParser().parseFromString(curTxt,'image/svg+xml');
  const paint=(attr,val)=>{doc.querySelectorAll('path,rect,circle,polygon,ellipse,line,polyline').forEach(el=>{
      el.setAttribute(attr,val);
  });};
  paint('fill',fillPick.value); paint('stroke',strokePick.value);
  const svg=doc.documentElement;
  wInp.value?svg.setAttribute('width',wInp.value):svg.removeAttribute('width');
  hInp.value?svg.setAttribute('height',hInp.value):svg.removeAttribute('height');
  curTxt=new XMLSerializer().serializeToString(doc);
  prevWrap.innerHTML=curTxt; updateDl();
}
['input','change'].forEach(evt=>{
  fillPick.addEventListener(evt,applyEdits);
  strokePick.addEventListener(evt,applyEdits);
  wInp.addEventListener(evt,applyEdits);
  hInp.addEventListener(evt,applyEdits);
});
document.getElementById('bgChk').onchange=e=>prevWrap.classList.toggle('checker',e.target.checked);
document.getElementById('copyBtn').onclick=()=>navigator.clipboard.writeText(curTxt);
function updateDl(){
  const blob=new Blob([curTxt],{type:'image/svg+xml'});
  document.getElementById('svgDl').href=URL.createObjectURL(blob);
  document.getElementById('svgDl').download='edited-'+curPath.split('/').pop();
}
document.getElementById('pngDl').onclick=async()=>{
   const canvas=document.createElement('canvas'), ctx=canvas.getContext('2d');
   const v=await canvg.Canvg.fromString(ctx,curTxt); await v.render();
   canvas.toBlob(b=>saveAs(b,'edited.png'));
};


const dragOv=document.getElementById('dragOv');
['dragenter','dragover'].forEach(ev=>document.addEventListener(ev,e=>{e.preventDefault(); dragOv.classList.add('show');}));
['dragleave','drop'].forEach(ev=>document.addEventListener(ev,e=>{e.preventDefault(); dragOv.classList.remove('show');}));
document.addEventListener('drop',e=>{
  const f=e.dataTransfer.files[0]; if(f&&f.type==='image/svg+xml'){
      f.text().then(t=>{curTxt=t; curPath=f.name; loadPrev(t); E.show();});
  }
});
</script>
</body></html>
