<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');

$defaults = [
    'id' => 'home-strength',
    'heading' => "飲食店経営者だから、\nできる提案がある。",
    'body' => '代表・新垣大作は、開業、運営、スタッフ育成、厨房づくり、設備投資、店舗売却までを実際に経験。現在も広島で沖縄料理店「新垣家」を経営しています。',
    'quote' => "「機械を売る」のではなく、\n「繁盛するお店づくり」を考える。",
    'images' => [],
    'scroll_duration' => 72,
];
$strength = array_replace($defaults, load_content('strength')[0] ?? []);
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $keptImages = array_values(array_filter(
            (array)($_POST['keep_images'] ?? []),
            fn($path): bool => is_string($path) && str_starts_with($path, 'uploads/') && is_file(__DIR__ . '/' . $path)
        ));
        $selectedCount = count(array_filter((array)($_FILES['images']['name'] ?? []), fn($name): bool => (string)$name !== ''));
        if (count($keptImages) + $selectedCount > 12) {
            throw new RuntimeException('OUR STRENGTHの写真は合計12枚までです。');
        }
        $newImages = upload_image_files('images', max(0, 12 - count($keptImages)));
        $strength = [
            'id' => 'home-strength',
            'heading' => trim((string)($_POST['heading'] ?? '')),
            'body' => trim((string)($_POST['body'] ?? '')),
            'quote' => trim((string)($_POST['quote'] ?? '')),
            'images' => array_slice(array_merge($keptImages, $newImages), 0, 12),
            'scroll_duration' => max(30, min(180, (int)($_POST['scroll_duration'] ?? 72))),
        ];
        if ($strength['heading'] === '' || $strength['body'] === '' || $strength['quote'] === '') {
            throw new RuntimeException('大見出し、本文、強調メッセージをすべて入力してください。');
        }
        save_content('strength', [$strength]);
        redirect('strength-admin.php?saved=1');
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
  <title>OUR STRENGTH管理｜管理画面</title>
  <link rel="stylesheet" href="assets/admin.css">
  <link rel="stylesheet" href="assets/admin-menu-fix.css?v=2">
  <link rel="stylesheet" href="assets/strength-admin.css?v=2">
  <script src="assets/strength-admin.js?v=2" defer></script>
</head>
<body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav></nav></aside>
<script src="assets/admin-nav.js?v=7" defer></script>
<main class="admin-main">
  <header><div><p>PRO CHUBO HIT OKINAWA</p><h1>OUR STRENGTH管理</h1></div><a href="index.php#strength" target="_blank">公開ページを確認 ↗</a></header>
  <?php if(isset($_GET['saved'])):?><p class="success">OUR STRENGTHを保存しました。</p><?php endif;?>
  <?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
  <section class="panel editor strength-editor">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
      <p class="section-label">02 / OUR STRENGTH</p>
      <label>大見出し<textarea name="heading" rows="3" required><?=e($strength['heading'])?></textarea><small>入力した改行を公開ページにも反映します。</small></label>
      <label>本文<textarea name="body" rows="7" required><?=e($strength['body'])?></textarea><small>空行を入れると段落が分かれます。</small></label>
      <div class="strength-images">
        <strong>登録写真</strong>
        <p class="hint">公開ページではモノトーンに変換し、横方向へゆっくり自動スクロールします。</p>
        <?php if(!empty($strength['images'])):?><div class="strength-image-list"><?php foreach($strength['images'] as $image):?><div class="strength-image-card" data-strength-image-card><img src="<?=e($image)?>" alt=""><label><span><input type="checkbox" name="keep_images[]" value="<?=e($image)?>" checked> この写真を使用する</span></label><button type="button" class="strength-image-delete" data-strength-image-delete>写真を削除</button></div><?php endforeach;?></div><?php endif;?>
        <label class="image-upload">写真を追加<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple data-strength-images><small>JPEG・PNG・WebP／1枚6MBまで・合計12枚まで。長辺1920pxを超える画像は比率を保って自動縮小します。</small></label>
        <div class="strength-image-preview" data-strength-preview></div>
        <label class="strength-speed">スクロール速度 <output data-strength-speed-output><?=e((string)($strength['scroll_duration']??72))?>秒</output><input type="range" name="scroll_duration" min="30" max="180" step="6" value="<?=e((string)($strength['scroll_duration']??72))?>" data-strength-speed><small>数字が大きいほど、ゆっくり流れます。</small></label>
      </div>
      <label>強調メッセージ<textarea name="quote" rows="3" required><?=e($strength['quote'])?></textarea><small>写真の下へ大きく表示します。</small></label>
      <button class="primary">OUR STRENGTHを保存する</button>
    </form>
  </section>
</main>
</body>
</html>
