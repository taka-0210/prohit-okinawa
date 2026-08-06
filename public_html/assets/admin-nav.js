(()=>{
 if(document.documentElement.dataset.adminNavReady)return;
 document.documentElement.dataset.adminNavReady='1';
 const style=document.createElement('link');style.rel='stylesheet';style.href='assets/admin-sidebar-v2.css';document.head.append(style);
 const helpStyle=document.createElement('link');helpStyle.rel='stylesheet';helpStyle.href='assets/admin-help.css?v=1';document.head.append(helpStyle);
 const logo=document.querySelector('.admin-shell aside .admin-logo');
 if(logo)logo.innerHTML='PRO CHUBO<strong>HIT OKINAWA</strong><small>CONTENT MANAGEMENT</small>';
 const nav=document.querySelector('.admin-shell aside nav');
 if(!nav)return;
 const path=location.pathname.split('/').pop()||'admin.php',tab=new URLSearchParams(location.search).get('tab')||'dashboard';
 const items=[['admin.php','dashboard','ダッシュボード'],['admin.php?tab=hero','hero','HEROスライド'],['hero-settings.php','hero-settings','HERO共通設定'],['home-admin.php','home','ホーム基本情報'],['strength-admin.php','strength','OUR STRENGTH'],['service-admin.php','services','サービス管理'],['maps-admin.php','maps','地図管理'],['works-admin.php','works','施工事例'],['news-admin.php','news','最新情報'],['company-admin.php','company','ブランド・会社情報'],['inquiries-admin.php','inquiries','お問い合わせ']];
 const current=path==='home-admin.php'?'home':path==='strength-admin.php'?'strength':path==='hero-settings.php'?'hero-settings':path==='service-admin.php'?'services':path==='maps-admin.php'?'maps':path==='works-admin.php'?'works':path==='news-admin.php'?'news':path==='inquiries-admin.php'?'inquiries':path==='company-admin.php'?'company':tab;
 nav.innerHTML=items.map(([href,key,label])=>`<a class="${current===key?'active':''}" href="${href}">${label}</a>`).join('');

 const help={
  hero:{title:'HEROスライドを管理',summary:'トップページ最上部の画像、見出し、リード文、公開状態をスライドごとに編集します。',steps:['編集するスライドを選び、文章と画像を設定します。','公開する場合は「公開する」をオンにして保存します。','色やドットなど全スライド共通の見え方は「HERO共通設定」で調整します。']},
  'hero-settings':{title:'HEROの見え方を共通設定',summary:'オーバーレイの色・濃度とドット柄を、すべてのHEROスライドへ共通適用します。',steps:['公開ページを別タブで開き、画像上の文字が読みやすい濃度へ調整します。','ドット柄の有無と濃度を設定します。','保存後、PCとスマートフォンの両方で見え方を確認します。']},
  home:{title:'ホームの基本情報を編集',summary:'トップページのABOUT USなど、サイトの紹介に使う基本文章を管理します。',steps:['見出しと本文を編集します。','改行位置を含めて公開ページの読みやすさを確認します。','保存後、「公開ページを確認」から反映内容を確認します。']},
  strength:{title:'OUR STRENGTHを編集',summary:'会社の強みを伝える見出し、本文、引用文、写真と表示速度を管理します。',steps:['見出し・本文・引用文を入力します。','使用する写真を登録し、必要に応じて表示順を整えます。','写真の流れる速度を調整し、公開ページで確認します。']},
  services:{title:'サービスカードと詳細ページを管理',summary:'トップページのサービスカードと、各サービス詳細ページの内容を一括管理します。',steps:['カードの名称、説明、背景画像、公開状態を設定します。','詳細ページのセクションごとに文章・画像・リンクを編集します。','並び順を整え、トップページと詳細ページの両方を確認します。']},
  maps:{title:'施工事例に使う地図を管理',summary:'掲載エリアの地図画像、表示倍率、ピンのまとまり方を設定します。',steps:['地図名と地図画像を登録します。','表示倍率を調整し、地図が枠内に収まるか確認します。','施工事例側でこの地図を選び、ピン位置を設定します。']},
  works:{title:'施工事例を登録・編集',summary:'施工写真、概要、掲載地図、ピン位置、外部リンクとトップページ表示を管理します。',steps:['基本情報と施工写真を登録し、写真の表示順を整えます。','掲載地図を選択し、プレビュー上でピン位置を設定します。','Googleマップ・Instagram・店舗サイトのリンクと公開状態を確認します。']},
  news:{title:'最新情報を作成・公開',summary:'お知らせの公開日、本文、画像やリンクを編集し、公開状態を管理します。',steps:['タイトル、カテゴリ、公開日を入力します。','本文ブロックへ文章・画像・リンクを追加します。','下書きで内容を確認し、準備ができたら公開します。']},
  company:{title:'ブランド・会社情報を管理',summary:'ロゴ、会社写真、所在地、連絡先、代表者、沿革など共通情報を編集します。',steps:['会社情報と連絡先を最新の内容へ更新します。','ロゴ・外観・内観写真を登録します。','変更後、トップページとお問い合わせページを確認します。']},
  inquiries:{title:'お問い合わせを確認',summary:'フォームから届いた相談内容と通知状況を確認します。',steps:['左側の一覧からお問い合わせを選択します。','氏名、連絡先、相談内容、受信日時を確認します。','確認したお問い合わせは既読として管理され、ダッシュボードの未読数へ反映されます。']}
 };
 const config=help[current],header=document.querySelector('.admin-main>header');
 if(config&&header){
  const panel=document.createElement('section');panel.className='admin-screen-help';
  panel.innerHTML=`<div class="admin-screen-help-intro"><small>SCREEN GUIDE</small><strong>${config.title}</strong><p>${config.summary}</p></div><details><summary>操作方法を見る</summary><ol>${config.steps.map(step=>`<li>${step}</li>`).join('')}</ol></details>`;
  header.insertAdjacentElement('afterend',panel);
 }
 if(path==='service-admin.php'){
  const tabStyle=document.createElement('link');tabStyle.rel='stylesheet';tabStyle.href='assets/service-admin-tabs.css?v=5';document.head.append(tabStyle);
  const tabScript=document.createElement('script');tabScript.src='assets/service-admin-tabs.js?v=5';document.head.append(tabScript);
 }
})();
