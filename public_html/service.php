<?php
require __DIR__ . '/lib.php';

$slug = (string) ($_GET['slug'] ?? 'kitchen-design-opening');
$allServices = array_values(array_filter(
    load_content('services'),
    static fn(array $item): bool => !empty($item['published'])
));
$service = null;
foreach ($allServices as $item) {
    if (($item['id'] ?? '') === $slug) {
        $service = $item;
        break;
    }
}
if (!$service) {
    http_response_code(404);
    exit('サービスページが見つかりません。');
}
$company = load_content('company')[0] ?? [];
$visibleSections = array_values(array_filter(
    array_slice($service['sections'] ?? [], 0, 5),
    static fn(array $section): bool => !array_key_exists('enabled', $section) || !empty($section['enabled'])
));
$visibleSectionCount = count($visibleSections);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($service['title']) ?>｜<?= e($company['company_name'] ?? APP_NAME) ?></title>
  <meta name="description" content="<?= e($service['lead']) ?>">
  <link rel="stylesheet" href="assets/service-page.css">
  <link rel="stylesheet" href="assets/service-navigation.css?v=1">
  <link rel="stylesheet" href="assets/service-header-fix.css?v=2">
</head>
<body>
<header class="service-header">
  <a class="service-brand" href="index.php">
    <?php if (!empty($company['logo'])): ?>
      <img src="<?= e($company['logo']) ?>" alt="<?= e($company['company_name'] ?? APP_NAME) ?>">
    <?php else: ?>
      PRO CHUBO<br><strong>HIT OKINAWA</strong>
    <?php endif; ?>
  </a>
  <nav><a href="index.php#services">サービス一覧</a><a href="index.php#works">施工事例</a><a class="contact-link" href="contact.php">お問い合わせ</a></nav>
</header>
<main>
  <section class="service-hero"><div><p><?= e($service['title_en'] ?? 'SERVICE') ?></p><h1><?= e($service['title']) ?></h1><p class="lead"><?= e($service['lead']) ?></p></div><span><?= str_pad((string) (array_search($service, $allServices, true) + 1), 2, '0', STR_PAD_LEFT) ?></span></section>
  <section class="service-intro"><p>OUR APPROACH</p><div><h2>お店の動きと未来から、<br>厨房を設計する。</h2><p><?= nl2br(e($service['intro'])) ?></p></div></section>
  <div class="service-story">
    <?php foreach ($visibleSections as $index => $section): ?>
      <section class="story-block">
        <div class="story-photo<?= empty($section['image']) ? ' is-placeholder' : '' ?>"<?= !empty($section['image']) ? ' style="background-image:url(' . e($section['image']) . ')"' : '' ?>><span>PHOTO <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span></div>
        <div class="story-text"><p><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?> / <?= str_pad((string) $visibleSectionCount, 2, '0', STR_PAD_LEFT) ?></p><h2><?= e($section['heading'] ?? '') ?></h2><p><?= nl2br(e($section['body'] ?? '')) ?></p></div>
      </section>
    <?php endforeach; ?>
  </div>
  <section class="other-services">
    <p>OTHER SERVICES</p><h2>ほかのサービス</h2>
    <nav>
      <?php foreach ($allServices as $index => $item): ?>
        <a href="service.php?slug=<?= e($item['id']) ?>"<?= ($item['id'] ?? '') === $slug ? ' aria-current="page"' : '' ?>><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><strong><?= e($item['title']) ?></strong></a>
      <?php endforeach; ?>
    </nav>
  </section>
  <section class="service-cta"><p>START YOUR RESTAURANT WITH US.</p><h2>お店づくりの構想から、<br>お気軽にご相談ください。</h2><a href="contact.php">お問い合わせへ →</a></section>
</main>
<footer><a href="index.php">← トップページへ戻る</a><small>© <?= date('Y') ?> PRO CHUBO HIT OKINAWA</small></footer>
</body>
</html>
