<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');

$defaults = [
    'id' => 'home-about',
    'about_kicker' => "BUILDING RESTAURANTS,\nBUILDING FUTURES.",
    'about_heading' => "厨房機器を売るだけでは、\nお店は完成しません。",
    'about_body' => "私たちは、飲食店オーナーの「こんなお店をつくりたい」という想いを形にする、店舗づくりのプロフェッショナルチームです。新規開業はもちろん、改装や設備の入れ替え、店舗の譲渡・買取まで、お店の状況やこれからの計画に寄り添いながら、最適な方法を一緒に考えます。\n\n現地調査、厨房レイアウト、CAD図面、厨房機器の選定、搬入・設置、内外装工事まで、店舗づくりに必要な工程を一つの窓口で対応します。複数の業者とのやり取りをできる限り減らし、計画全体を見渡しながら進めることで、開業準備にかかる負担や行き違いを抑えます。\n\n大切にしているのは、機器を販売することだけではなく、そのお店に本当に必要な環境をつくること。業態やメニュー、厨房の広さ、スタッフの動線、予算を丁寧に確認し、営業のしやすさや将来の設備更新まで見据えてご提案します。完成した瞬間だけでなく、その先も長く愛されるお店づくりを支えます。",
];
$home = array_replace($defaults, load_content('home')[0] ?? []);
$home = array_replace([
    'about_kicker_en' => '',
    'about_heading_en' => '',
    'about_body_en' => '',
    'about_kicker_zh_tw' => '',
    'about_heading_zh_tw' => '',
    'about_body_zh_tw' => '',
], $home);
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $home = [
            'id' => 'home-about',
            'about_kicker' => trim((string)($_POST['about_kicker'] ?? '')),
            'about_heading' => trim((string)($_POST['about_heading'] ?? '')),
            'about_body' => trim((string)($_POST['about_body'] ?? '')),
            'about_kicker_en' => trim((string)($_POST['about_kicker_en'] ?? '')),
            'about_heading_en' => trim((string)($_POST['about_heading_en'] ?? '')),
            'about_body_en' => trim((string)($_POST['about_body_en'] ?? '')),
            'about_kicker_zh_tw' => trim((string)($_POST['about_kicker_zh_tw'] ?? '')),
            'about_heading_zh_tw' => trim((string)($_POST['about_heading_zh_tw'] ?? '')),
            'about_body_zh_tw' => trim((string)($_POST['about_body_zh_tw'] ?? '')),
        ];
        if (in_array('', $home, true)) {
            throw new RuntimeException('すべての項目を入力してください。');
        }
        save_content('home', [$home]);
        redirect('home-admin.php?saved=1');
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ホーム基本情報｜管理画面</title>
  <link rel="stylesheet" href="assets/admin.css">
  <link rel="stylesheet" href="assets/home-admin.css?v=1">
  <link rel="stylesheet" href="assets/admin-menu-fix.css?v=2">
</head>
<body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav></nav></aside>
<script src="assets/admin-nav.js?v=8" defer></script>
<main class="admin-main">
  <header><div><p>PRO CHUBO HIT OKINAWA</p><h1>ホーム基本情報</h1></div><a href="index.php#about" target="_blank">公開ページを確認 ↗</a></header>
  <?php if(isset($_GET['saved'])):?><p class="success">ABOUT USを保存しました。</p><?php endif;?>
  <?php if($error):?><p class="error"><?= e($error) ?></p><?php endif;?>
  <section class="panel editor home-editor">
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <p class="section-label">01 / ABOUT US</p>
      <label>英字キャッチコピー<textarea name="about_kicker" rows="3" required><?= e($home['about_kicker']) ?></textarea><small>入力した改行を公開ページにも反映します。</small></label>
      <label>大見出し<textarea name="about_heading" rows="3" required><?= e($home['about_heading']) ?></textarea><small>読みやすい位置で改行してください。</small></label>
      <label>本文<textarea name="about_body" rows="10" required><?= e($home['about_body']) ?></textarea><small>空行を入れると段落が分かれます。通常の改行もそのまま反映されます。</small></label>
      <h2>English</h2>
      <label>English kicker<textarea name="about_kicker_en" rows="3"><?= e($home['about_kicker_en']) ?></textarea></label>
      <label>English heading<textarea name="about_heading_en" rows="3"><?= e($home['about_heading_en']) ?></textarea></label>
      <label>English body<textarea name="about_body_en" rows="10"><?= e($home['about_body_en']) ?></textarea></label>
      <h2>繁體中文</h2>
      <label>繁體中文・短句<textarea name="about_kicker_zh_tw" rows="3"><?= e($home['about_kicker_zh_tw']) ?></textarea></label>
      <label>繁體中文・標題<textarea name="about_heading_zh_tw" rows="3"><?= e($home['about_heading_zh_tw']) ?></textarea></label>
      <label>繁體中文・本文<textarea name="about_body_zh_tw" rows="10"><?= e($home['about_body_zh_tw']) ?></textarea></label>
      <button class="primary">ホーム基本情報を保存する</button>
    </form>
  </section>
</main>
</body>
</html>
