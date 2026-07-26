<?php
require __DIR__ . '/lib.php';

$company = load_content('company')[0] ?? [];
$allNews = published(load_content('news'));
usort($allNews, static fn(array $a, array $b): int => strcmp(
    (string)($b['published_at'] ?? ''),
    (string)($a['published_at'] ?? '')
));
$archives = [];
foreach ($allNews as $item) {
    $month = substr((string)($item['published_at'] ?? ''), 0, 7);
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) continue;
    $archives[$month] = ($archives[$month] ?? 0) + 1;
}
$selectedMonth = (string)($_GET['month'] ?? '');
if ($selectedMonth !== '' && !isset($archives[$selectedMonth])) {
    $selectedMonth = '';
}
$news = $selectedMonth === ''
    ? $allNews
    : array_values(array_filter($allNews, static fn(array $item): bool =>
        str_starts_with((string)($item['published_at'] ?? ''), $selectedMonth)
    ));
$newsThumbnail = static function (array $item): string {
    foreach ((array)($item['blocks'] ?? []) as $block) {
        if (($block['type'] ?? '') === 'image' && !empty($block['image'])) {
            return (string)$block['image'];
        }
    }
    return '';
};
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>最新情報｜<?= e($company['company_name'] ?? APP_NAME) ?></title>
  <meta name="description" content="プロ厨房HIT沖縄からのお知らせ、施工事例やサービスに関する最新情報をご案内します。">
  <link rel="stylesheet" href="assets/news-page.css?v=4">
  <link rel="stylesheet" href="assets/news-archive-links.css?v=8">
  <link rel="stylesheet" href="assets/site-width.css?v=1">
</head>
<body>
<header class="news-header">
  <a class="news-brand" href="index.php">
    <?php if (!empty($company['logo'])): ?>
      <img src="<?= e($company['logo']) ?>" alt="<?= e($company['company_name'] ?? APP_NAME) ?>">
    <?php else: ?>
      <strong><?= e(APP_NAME) ?></strong>
    <?php endif; ?>
  </a>
  <nav><a href="index.php#services">サービス</a><a href="works.php">施工事例</a><a class="contact-link" href="contact.php">お問い合わせ</a></nav>
</header>
<main>
  <section class="news-hero">
    <p>NEWS &amp; INFORMATION</p>
    <h1>最新情報</h1>
    <span>プロ厨房HIT沖縄からの<br>お知らせをご案内します。</span>
  </section>
  <section class="news-archive">
    <div class="news-archive-layout">
      <div class="news-results">
        <div class="news-result-head"><p><?= $selectedMonth === '' ? 'ALL NEWS' : e(str_replace('-', '.', $selectedMonth)) ?></p><span><?= count($news) ?> ARTICLES</span></div>
        <div class="news-list">
          <?php if ($news === []): ?><p class="news-empty">現在、公開中のお知らせはありません。</p><?php endif; ?>
          <?php foreach ($news as $item): ?>
          <?php $thumbnail = $newsThumbnail($item); ?>
          <a href="news-detail.php?id=<?= rawurlencode((string)($item['id'] ?? '')) ?>"><article id="<?= e($item['id'] ?? '') ?>" class="<?= $thumbnail !== '' ? 'has-thumbnail' : '' ?>">
            <div class="news-meta"><time datetime="<?= e($item['published_at'] ?? '') ?>"><?= e($item['published_at'] ?? '') ?></time><span><?= e($item['category'] ?? 'お知らせ') ?></span></div>
            <div class="news-copy"><h2><?= e($item['title'] ?? '') ?></h2></div>
            <figure class="news-thumbnail<?= $thumbnail === '' ? ' is-placeholder' : '' ?>"><?php if ($thumbnail !== ''): ?><img src="<?= e($thumbnail) ?>" alt="" loading="lazy"><?php else: ?><span>NEWS</span><?php endif; ?></figure>
          </article></a>
          <?php endforeach; ?>
        </div>
      </div>
      <aside class="news-archive-menu">
        <p>ARCHIVE</p>
        <nav>
          <a href="news.php"<?= $selectedMonth === '' ? ' aria-current="page"' : '' ?>><span>すべて</span><small><?= count($allNews) ?></small></a>
          <?php foreach ($archives as $month => $count): ?>
          <a href="?month=<?= e($month) ?>"<?= $selectedMonth === $month ? ' aria-current="page"' : '' ?>><span><?= e(substr($month, 0, 4)) ?>年 <?= e((string)(int)substr($month, 5, 2)) ?>月</span><small><?= $count ?></small></a>
          <?php endforeach; ?>
        </nav>
      </aside>
    </div>
  </section>
  <section class="news-cta"><p>START YOUR RESTAURANT WITH US.</p><h2>お店づくりのことなら、<br>お気軽にご相談ください。</h2><a href="contact.php">お問い合わせへ →</a></section>
</main>
<footer><a href="index.php">← トップページへ戻る</a><small>© <?= date('Y') ?> <?= e($company['company_name_en'] ?? 'PRO CHUBO HIT OKINAWA') ?></small></footer>
</body>
</html>
