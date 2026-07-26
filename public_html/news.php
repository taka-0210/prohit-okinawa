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
$archiveYears = [];
foreach ($archives as $month => $count) {
    $year = substr($month, 0, 4);
    $archiveYears[$year][$month] = $count;
}
$selectedMonth = (string)($_GET['month'] ?? '');
if ($selectedMonth !== '' && !isset($archives[$selectedMonth])) {
    $selectedMonth = '';
}
$filteredNews = $selectedMonth === ''
    ? $allNews
    : array_values(array_filter($allNews, static fn(array $item): bool =>
        str_starts_with((string)($item['published_at'] ?? ''), $selectedMonth)
    ));
$perPage = 9;
$totalArticles = count($filteredNews);
$totalPages = max(1, (int)ceil($totalArticles / $perPage));
$currentPage = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
$news = array_slice($filteredNews, ($currentPage - 1) * $perPage, $perPage);
$pageUrl = static function (int $page) use ($selectedMonth): string {
    $params = [];
    if ($selectedMonth !== '') $params['month'] = $selectedMonth;
    if ($page > 1) $params['page'] = $page;
    return 'news.php' . ($params === [] ? '' : '?' . http_build_query($params));
};
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
  <link rel="stylesheet" href="assets/news-archive-links.css?v=9">
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
        <div class="news-result-head"><p><?= $selectedMonth === '' ? 'ALL NEWS' : e(str_replace('-', '.', $selectedMonth)) ?></p><span><?= $totalArticles ?> ARTICLES</span></div>
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
        <?php if ($totalPages > 1): ?>
        <nav class="news-pagination" aria-label="記事一覧のページ送り">
          <?php if ($currentPage > 1): ?><a href="<?= e($pageUrl($currentPage - 1)) ?>">← 前へ</a><?php else: ?><span></span><?php endif; ?>
          <strong><?= $currentPage ?> / <?= $totalPages ?></strong>
          <?php if ($currentPage < $totalPages): ?><a href="<?= e($pageUrl($currentPage + 1)) ?>">次へ →</a><?php else: ?><span></span><?php endif; ?>
        </nav>
        <?php endif; ?>
      </div>
      <aside class="news-archive-menu">
        <p>ARCHIVE</p>
        <form class="archive-select" action="news.php" method="get">
          <label for="archive-month">年月を選択</label>
          <select id="archive-month" name="month" onchange="this.form.submit()">
            <option value="">すべての記事（<?= count($allNews) ?>）</option>
            <?php foreach ($archives as $month => $count): ?>
            <option value="<?= e($month) ?>"<?= $selectedMonth === $month ? ' selected' : '' ?>><?= e(substr($month, 0, 4)) ?>年 <?= e((string)(int)substr($month, 5, 2)) ?>月（<?= $count ?>）</option>
            <?php endforeach; ?>
          </select>
        </form>
        <div class="archive-years">
          <a class="archive-all" href="news.php"<?= $selectedMonth === '' ? ' aria-current="page"' : '' ?>><span>すべての記事</span><small><?= count($allNews) ?></small></a>
          <?php foreach ($archiveYears as $year => $months): ?>
          <?php $yearTotal = array_sum($months); $yearSelected = str_starts_with($selectedMonth, $year . '-'); ?>
          <details<?= $yearSelected || ($selectedMonth === '' && $year === array_key_first($archiveYears)) ? ' open' : '' ?>>
            <summary><span><?= e($year) ?>年</span><small><?= $yearTotal ?></small></summary>
            <nav>
              <?php foreach ($months as $month => $count): ?>
              <a href="?month=<?= e($month) ?>"<?= $selectedMonth === $month ? ' aria-current="page"' : '' ?>><span><?= e((string)(int)substr($month, 5, 2)) ?>月</span><small><?= $count ?></small></a>
              <?php endforeach; ?>
            </nav>
          </details>
          <?php endforeach; ?>
        </div>
      </aside>
    </div>
  </section>
  <section class="news-cta"><p>START YOUR RESTAURANT WITH US.</p><h2>お店づくりのことなら、<br>お気軽にご相談ください。</h2><a href="contact.php">お問い合わせへ →</a></section>
</main>
<footer><a href="index.php">← トップページへ戻る</a><small>© <?= date('Y') ?> <?= e($company['company_name_en'] ?? 'PRO CHUBO HIT OKINAWA') ?></small></footer>
</body>
</html>
