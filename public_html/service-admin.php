<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');

$id = (string)($_GET['id'] ?? 'kitchen-design-opening');
$isNew = $id === 'new';
$items = load_content('services');
$service = null;
$serviceIndex = null;
$error = '';
foreach ($items as $index => $item) {
    if (($item['id'] ?? '') === $id) {
        $service = $item;
        $serviceIndex = $index;
    }
}
if ($isNew) {
    $service = [
        'id' => '',
        'title' => '',
        'title_en' => '',
        'lead' => '',
        'intro_heading' => '',
        'intro' => '',
        'sections' => [],
        'published' => false,
        'content_revision' => 2,
    ];
} elseif (!$service) {
    http_response_code(404);
    exit('サービスが見つかりません。');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $sections = [];
        for ($index = 0; $index < 5; $index++) {
            $current = (string)($service['sections'][$index]['image'] ?? '');
            $sections[] = [
                'heading' => trim((string)($_POST['section_heading'][$index] ?? '')),
                'body' => trim((string)($_POST['section_body'][$index] ?? '')),
                'image' => upload_image('section_image_' . $index, $current),
                'enabled' => isset($_POST['section_enabled'][$index]),
            ];
        }
        $savedId = $isNew ? 'service-' . bin2hex(random_bytes(5)) : $id;
        $service = [
            'id' => $savedId,
            'title' => trim((string)($_POST['title'] ?? '')),
            'title_en' => trim((string)($_POST['title_en'] ?? '')),
            'lead' => trim((string)($_POST['lead'] ?? '')),
            'intro_heading' => trim((string)($_POST['intro_heading'] ?? '')),
            'intro' => trim((string)($_POST['intro'] ?? '')),
            'sections' => $sections,
            'published' => isset($_POST['published']),
            'content_revision' => (int)($service['content_revision'] ?? 0),
        ];
        if ($service['title'] === '') throw new RuntimeException('サービス名は必須です。');
        if ($isNew) {
            $items[] = $service;
        } else {
            $items[$serviceIndex] = $service;
        }
        save_content('services', $items);
        redirect('service-admin.php?id=' . rawurlencode($savedId) . '&saved=1');
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
  <meta name="csrf-token" content="<?=e(csrf_token())?>">
  <title>サービス管理</title>
  <link rel="stylesheet" href="assets/admin.css">
  <link rel="stylesheet" href="assets/admin-menu-fix.css">
  <link rel="stylesheet" href="assets/service-admin.css?v=4">
</head>
<body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav></nav></aside>
<script src="assets/admin-nav.js?v=5" defer></script>
<main class="admin-main">
  <header><div><p>PRO CHUBO HIT OKINAWA</p><h1><?= $isNew?'新規サービス登録':'サービス管理' ?></h1></div><?php if(!$isNew):?><a href="service.php?slug=<?=e($id)?>" target="_blank">公開ページを確認 ↗</a><?php endif;?></header>
  <?php if(isset($_GET['saved'])):?><p class="success">保存しました。</p><?php endif;?>
  <?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <section class="panel editor service-basics">
      <h2>基本情報</h2>
      <div class="fields"><label>サービス名<input name="title" required value="<?=e($service['title'])?>"></label><label>英語表記<input name="title_en" value="<?=e($service['title_en']??'')?>"></label></div>
      <label>リード文<textarea name="lead" rows="3"><?=e($service['lead']??'')?></textarea></label>
      <label>導入見出し<input name="intro_heading" value="<?=e($service['intro_heading']??$service['title']??'')?>"></label>
      <label>導入文<textarea name="intro" rows="5"><?=e($service['intro']??'')?></textarea></label>
      <label class="publication-toggle">
        <input type="checkbox" name="published" <?=!empty($service['published'])?'checked':''?>>
        <span class="publication-switch" aria-hidden="true"></span>
        <span class="publication-state">
          <strong class="state-public">公開</strong>
          <strong class="state-private">非公開</strong>
          <small class="state-public">公開サイトにこのサービスを表示します。</small>
          <small class="state-private">管理画面にのみ保存し、公開サイトには表示しません。</small>
        </span>
      </label>
    </section>
    <div class="service-blocks">
      <?php for($index=0;$index<5;$index++):$section=$service['sections'][$index]??[];$sectionEnabled=!array_key_exists('enabled',$section)||!empty($section['enabled']);?>
      <section class="panel editor service-block">
        <div class="block-number">PHOTO &amp; TEXT <?=str_pad((string)($index+1),2,'0',STR_PAD_LEFT)?></div>
        <div class="block-media">
          <?php if(!empty($section['image'])):?><img src="<?=e($section['image'])?>" alt="登録画像"><?php else:?><div>NO IMAGE</div><?php endif;?>
          <label>写真を差し替える<input type="file" name="section_image_<?=$index?>" accept="image/jpeg,image/png,image/webp"><small>※長辺1920pxを超える画像は、比率を保って自動縮小します。</small></label>
        </div>
        <div class="block-copy">
          <label class="block-enabled"><input type="checkbox" name="section_enabled[<?=$index?>]" <?=$sectionEnabled?'checked':''?>>この項目を使用する</label>
          <label>見出し<input name="section_heading[]" value="<?=e($section['heading']??'')?>"></label>
          <label>本文<textarea name="section_body[]" rows="8"><?=e($section['body']??'')?></textarea></label>
        </div>
      </section>
      <?php endfor;?>
    </div>
    <div class="save-bar"><button class="primary"><?= $isNew?'新規サービスを登録する':'サービスページを保存する' ?></button></div>
  </form>
</main>
</body>
</html>
