(()=>{
 const style=document.createElement('link');style.rel='stylesheet';style.href='assets/admin-sidebar-v2.css';document.head.append(style);
 const logo=document.querySelector('.admin-shell aside .admin-logo');
 if(logo)logo.innerHTML='PRO CHUBO<strong>HIT OKINAWA</strong><small>CONTENT MANAGEMENT</small>';
 const nav=document.querySelector('.admin-shell aside nav');
 if(!nav)return;
 const path=location.pathname.split('/').pop()||'admin.php',tab=new URLSearchParams(location.search).get('tab')||'dashboard';
 const items=[['admin.php','dashboard','ダッシュボード'],['admin.php?tab=hero','hero','HEROスライド'],['hero-settings.php','hero-settings','HERO共通設定'],['service-admin.php','services','サービスページ'],['works-admin.php','works','施工事例'],['admin.php?tab=news','news','最新情報'],['inquiries-admin.php','inquiries','お問い合わせ'],['company-admin.php','company','ブランド・会社情報']];
 const current=path==='hero-settings.php'?'hero-settings':path==='service-admin.php'?'services':path==='works-admin.php'?'works':path==='inquiries-admin.php'?'inquiries':path==='company-admin.php'?'company':tab;
 nav.innerHTML=items.map(([href,key,label])=>`<a class="${current===key?'active':''}" href="${href}">${label}</a>`).join('');
 if(path==='service-admin.php'){
  const tabStyle=document.createElement('link');tabStyle.rel='stylesheet';tabStyle.href='assets/service-admin-tabs.css?v=2';document.head.append(tabStyle);
  const tabScript=document.createElement('script');tabScript.src='assets/service-admin-tabs.js?v=3';document.head.append(tabScript);
 }
})();
