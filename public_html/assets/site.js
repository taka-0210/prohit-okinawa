document.addEventListener('DOMContentLoaded',()=>{
 const slides=[...document.querySelectorAll('.slide')],counter=document.querySelector('[data-counter]'); let current=0,timer;
 const show=n=>{if(!slides.length)return;current=(n+slides.length)%slides.length;slides.forEach((s,i)=>{s.classList.toggle('active',i===current);s.setAttribute('aria-hidden',i===current?'false':'true')});if(counter)counter.textContent=String(current+1).padStart(2,'0')+' / '+String(slides.length).padStart(2,'0')};
 const start=()=>{if(!matchMedia('(prefers-reduced-motion: reduce)').matches)timer=setInterval(()=>show(current+1),6500)};
 document.querySelector('[data-next]')?.addEventListener('click',()=>{clearInterval(timer);show(current+1)});document.querySelector('[data-prev]')?.addEventListener('click',()=>{clearInterval(timer);show(current-1)});start();
 const toggle=document.querySelector('.nav-toggle'),nav=document.querySelector('#nav');toggle?.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')==='true';toggle.setAttribute('aria-expanded',String(!open));nav?.classList.toggle('open',!open)});
});
