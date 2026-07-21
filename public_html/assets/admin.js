document.head.insertAdjacentHTML('beforeend','<link rel="stylesheet" href="assets/admin-menu-fix.css">');
document.head.insertAdjacentHTML('beforeend','<link rel="stylesheet" href="assets/admin-media-status.css">');
document.head.insertAdjacentHTML('beforeend','<link rel="stylesheet" href="assets/admin-hero-fields.css">');
document.head.insertAdjacentHTML('beforeend','<link rel="stylesheet" href="assets/admin-common-effects.css">');
const adminNavScript=document.createElement('script');adminNavScript.src='assets/admin-nav.js?v=3';document.head.append(adminNavScript);
const q=s=>document.querySelector(s);
const backgroundColorInput=q('input[name="color"]');
if(backgroundColorInput){const fields=backgroundColorInput.closest('.fields'),label=backgroundColorInput.closest('label'),hidden=document.createElement('input');hidden.type='hidden';hidden.name='color';hidden.value=backgroundColorInput.value||'#14283d';fields?.append(hidden);label?.remove();fields?.classList.add('single-field')}
const overlayColorInput=q('input[name="overlay"]');
if(overlayColorInput){const value=document.createElement('output');value.className='color-value';value.textContent=overlayColorInput.value.toUpperCase();overlayColorInput.insertAdjacentElement('afterend',value);overlayColorInput.addEventListener('input',()=>value.textContent=overlayColorInput.value.toUpperCase())}
function update(){const preview=q('[data-preview]');if(!preview)return;preview.style.setProperty('--preview-overlay',q('[name=overlay]')?.value||'#102a43');preview.style.setProperty('--preview-opacity',(q('[data-overlay]')?.value||35)/100);q('[data-overlay-out]').textContent=(q('[data-overlay]')?.value||35)+'%';q('[data-dots-out]').textContent=(q('[data-dots]')?.value||18)+'%'}
q('[data-overlay]')?.addEventListener('input',update);q('[name=overlay]')?.addEventListener('input',update);q('[data-dots]')?.addEventListener('input',update);update();

if(new URLSearchParams(location.search).get('tab')==='hero'){
 const heroEditId=new URLSearchParams(location.search).get('edit');
 if(heroEditId){['input[name="overlay"]','input[name="overlay_opacity"]','input[name="dots"]','input[name="dots_opacity"]'].forEach(selector=>document.querySelector(selector)?.closest('label')?.classList.add('effect-setting-hidden'));document.querySelector('[data-preview]')?.classList.add('effect-setting-hidden')}
 fetch('admin-media-status.php',{credentials:'same-origin'}).then(r=>r.ok?r.json():Promise.reject()).then(data=>{
  const rows=[...document.querySelectorAll('.list tbody tr')];
  data.items.forEach((item,index)=>{const cell=rows[index]?.querySelector('td:first-child');if(!cell)return;const status=document.createElement('div');status.className='media-state '+(item.exists?'registered':'empty');status.innerHTML=item.exists?`<img src="${item.image}" alt=""><span>画像登録済み<small>${item.image.split('/').pop()}</small></span>`:'<span>画像未登録</span>';cell.append(status)});
  const editId=new URLSearchParams(location.search).get('edit'),current=data.items.find(item=>item.id===editId),input=document.querySelector('input[type=file][name=image]'),firstHero=data.items.find(item=>item.published)||data.items[0];
  if(firstHero){const firstIndex=data.items.findIndex(item=>item.id===firstHero.id),firstCell=rows[firstIndex]?.querySelector('td:first-child');firstCell?.insertAdjacentHTML('beforeend','<span class="common-effects-badge">共通エフェクト設定</span>')}
  if(editId&&firstHero){const form=document.querySelector('.editor form'),notice=document.createElement('div');notice.className='common-effects-notice';if(editId===firstHero.id){notice.innerHTML='<strong>スライダー共通設定</strong><p>オーバーレイ色・濃度・ドット柄・ドット濃度は、ここで設定した値が全HEROへ適用されます。</p>'}else{notice.innerHTML=`<strong>エフェクトは1枚目で共通管理しています</strong><p><a href="?tab=hero&edit=${encodeURIComponent(firstHero.id)}">共通エフェクト設定を編集する →</a></p>`;['input[name="overlay"]','input[name="overlay_opacity"]','input[name="dots"]','input[name="dots_opacity"]'].forEach(selector=>document.querySelector(selector)?.closest('label')?.classList.add('effect-setting-hidden'));document.querySelector('[data-preview]')?.classList.add('effect-setting-hidden')}form?.prepend(notice)}
  if(input){const box=document.createElement('div');box.className='current-media '+(current?.exists?'registered':'empty');box.innerHTML=current?.exists?`<img src="${current.image}" alt="現在登録中のHERO画像"><div><strong>現在の登録画像</strong><small>${current.image.split('/').pop()}</small><p>新しいファイルを選択すると、この画像を差し替えます。</p></div>`:'<div><strong>画像は未登録です</strong><p>ファイルを選択して保存してください。</p></div>';input.closest('label')?.before(box)}
  document.querySelectorAll('.common-effects-notice:not(.independent-effects-notice)').forEach(element=>element.remove());
  document.querySelectorAll('.common-effects-badge').forEach(element=>element.remove());
 }).catch(()=>{});
}
