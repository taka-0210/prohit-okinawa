<?php
require __DIR__ . '/lib.php';

$company = load_content('company')[0] ?? [];
$id = (string)($_GET['id'] ?? '');
$works = published(load_content('works'));
$work = null;
$workIndex = null;
foreach ($works as $index => $item) {
    if (($item['id'] ?? '') !== $id) continue;
    $work = $item;
    $workIndex = $index;
    break;
}
if (!$work) {
    http_response_code(404);
    exit('施工事例が見つかりません。');
}
$images = work_images($work);
$maps = load_content('maps');
$map = null;
foreach ($maps as $item) {
    if (($item['id'] ?? '') === ($work['map_id'] ?? '')) {
        $map = $item;
        break;
    }
}
$googleMapsUrl = (string)($work['google_maps_url'] ?? '');
if ($googleMapsUrl === '' && !empty($work['address'])) {
    $googleMapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string)$work['address']);
}
$previous = $workIndex !== null && $workIndex > 0 ? $works[$workIndex - 1] : null;
$next = $workIndex !== null && $workIndex < count($works) - 1 ? $works[$workIndex + 1] : null;
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($work['title']) ?>｜施工事例｜<?= e($company['company_name']??APP_NAME) ?></title>
  <meta name="description" content="<?= e(mb_substr((string)($work['summary']??''),0,120)) ?>">
  <link rel="stylesheet" href="assets/works-page.css?v=1">
  <link rel="stylesheet" href="assets/work-gallery.css?v=2">
  <link rel="stylesheet" href="assets/work-detail.css?v=4">
  <link rel="stylesheet" href="assets/site-width.css?v=1">
  <script src="assets/work-gallery.js?v=3" defer></script>
</head>
<body>
<header class="works-header">
  <a class="works-brand" href="index.php">
    <?php if(!empty($company['logo'])): ?><img src="<?= e($company['logo']) ?>" alt="<?= e($company['company_name']??APP_NAME) ?>">
    <?php else: ?><strong><?= e(APP_NAME) ?></strong><?php endif; ?>
  </a>
  <nav><a href="index.php#services">サービス</a><a href="works.php">施工事例一覧</a><a class="contact-link" href="contact.php">お問い合わせ</a></nav>
</header>
<main>
  <section class="work-detail-hero">
    <div class="work-detail-heading">
      <a href="works.php">WORKS / 施工事例一覧へ</a>
      <p><?= e($work['category']??'') ?><?= !empty($work['area'])?' / '.e($work['area']):'' ?></p>
      <h1><?= e($work['title']) ?></h1>
      <?php if(!empty($work['designer'])): ?><dl><dt>DESIGNER</dt><dd><?= e($work['designer']) ?></dd></dl><?php endif; ?>
    </div>
    <div class="work-hero-photo<?= count($images)>1?' has-photo-rail':'' ?>">
      <?php if($images): ?>
      <button class="work-gallery-trigger work-hero-primary" type="button" data-title="<?= e($work['title']) ?>" data-images="<?= e(json_encode($images,JSON_UNESCAPED_SLASHES)) ?>" data-start-index="0">
        <img src="<?= e($images[0]) ?>" alt="<?= e($work['title']) ?>">
        <?php if(count($images)>1): ?><span><?= count($images) ?> PHOTOS　写真を見る</span><?php endif; ?>
      </button>
      <?php if(count($images)>1): ?><div class="work-hero-photo-rail" aria-label="施工写真一覧"><?php foreach($images as $imageIndex=>$image): ?><button class="work-gallery-trigger" type="button" data-title="<?= e($work['title']) ?>" data-images="<?= e(json_encode($images,JSON_UNESCAPED_SLASHES)) ?>" data-start-index="<?= $imageIndex ?>" aria-label="施工写真 <?= $imageIndex+1 ?> を開く"><img src="<?= e($image) ?>" alt="" loading="lazy"></button><?php endforeach; ?></div><?php endif; ?>
      <?php else: ?><div class="is-placeholder"><span>NO IMAGE</span></div><?php endif; ?>
    </div>
  </section>

  <section class="work-detail-main">
    <div class="work-detail-content">
      <div class="work-description">
        <p class="detail-label">PROJECT STORY</p>
        <?php if(!empty($work['summary'])): ?><div><?= nl2br(e($work['summary'])) ?></div>
        <?php else: ?><p class="detail-empty">施工内容の詳細は準備中です。</p><?php endif; ?>
      </div>
      <aside class="work-information">
        <p class="detail-label">PROJECT INFORMATION</p>
        <dl>
          <?php if(!empty($work['category'])): ?><div><dt>業種</dt><dd><?= e($work['category']) ?></dd></div><?php endif; ?>
          <?php if(!empty($work['area'])): ?><div><dt>地域</dt><dd><?= e($work['area']) ?></dd></div><?php endif; ?>
          <?php if(!empty($work['designer'])): ?><div><dt>デザイナー</dt><dd><?= e($work['designer']) ?></dd></div><?php endif; ?>
          <?php if(!empty($work['address'])||$googleMapsUrl!==''): ?><div><dt>住所</dt><dd><?php if($googleMapsUrl!==''): ?><a href="<?= e($googleMapsUrl) ?>" target="_blank" rel="noopener noreferrer"><?= !empty($work['address'])?e($work['address']):'Googleマップを開く' ?> ↗</a><?php else: ?><?= e($work['address']) ?><?php endif; ?></dd></div><?php endif; ?>
          <?php if($map): ?><div><dt>掲載地図</dt><dd><?= e($map['title']??'') ?></dd></div><?php endif; ?>
        </dl>
        <?php if(!empty($work['instagram_url'])||!empty($work['website_url'])): ?>
        <div class="work-external-links">
          <?php if(!empty($work['instagram_url'])): ?><a href="<?= e($work['instagram_url']) ?>" target="_blank" rel="noopener noreferrer">Instagram ↗</a><?php endif; ?>
          <?php if(!empty($work['website_url'])): ?><a href="<?= e($work['website_url']) ?>" target="_blank" rel="noopener noreferrer">店舗ホームページ ↗</a><?php endif; ?>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </section>

  <nav class="work-detail-nav" aria-label="施工事例の前後移動">
    <?php if($previous): ?><a href="work-detail.php?id=<?= rawurlencode((string)$previous['id']) ?>"><small>PREVIOUS</small><strong>← <?= e($previous['title']) ?></strong></a><?php else: ?><span></span><?php endif; ?>
    <a class="work-list-link" href="works.php">施工事例一覧</a>
    <?php if($next): ?><a class="next" href="work-detail.php?id=<?= rawurlencode((string)$next['id']) ?>"><small>NEXT</small><strong><?= e($next['title']) ?> →</strong></a><?php else: ?><span></span><?php endif; ?>
  </nav>
  <section class="works-cta"><p>START YOUR RESTAURANT WITH US.</p><h2>次のお店づくりを、<br>一緒に始めませんか。</h2><a href="contact.php">お問い合わせへ →</a></section>
</main>
<footer><a href="index.php">トップページへ戻る</a><small>© <?= date('Y') ?> <?= e($company['company_name_en']??'PRO CHUBO HIT OKINAWA') ?></small></footer>
</body>
</html>
