<?php
require __DIR__ . '/lib.php';

$company = load_content('company')[0] ?? [];
$news = published(load_content('news'));
usort($news, static fn(array $a, array $b): int => strcmp(
    (string)($b['published_at'] ?? ''),
    (string)($a['published_at'] ?? '')
));
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>最新情報｜<?= e($company['company_name'] ?? APP_NAME) ?></title>
  <meta name="description" content="プロ厨房HIT沖縄からのお知らせ、施工事例やサービスに関する最新情報をご案内します。">
  <link rel="stylesheet" href="assets/news-page.css">
  <link rel="stylesheet" href="assets/news-archive-links.css">
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
    <div class="archive-heading"><p>ALL NEWS</p><h2>お知らせ一覧</h2><span><?= count($news) ?> ARTICLES</span></div>
    <div class="news-list">
      <?php if ($news === []): ?><p class="news-empty">現在、公開中のお知らせはありません。</p><?php endif; ?>
      <?php foreach ($news as $item): ?>
      <a href="news-detail.php?id=<?= rawurlencode((string)($item['id'] ?? '')) ?>"><article id="<?= e($item['id'] ?? '') ?>">
        <div class="news-meta"><time datetime="<?= e($item['published_at'] ?? '') ?>"><?= e($item['published_at'] ?? '') ?></time><span><?= e($item['category'] ?? 'お知らせ') ?></span></div>
        <div class="news-copy"><h2><?= e($item['title'] ?? '') ?></h2></div>
      </article></a>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="news-cta"><p>START YOUR RESTAURANT WITH US.</p><h2>お店づくりのことなら、<br>お気軽にご相談ください。</h2><a href="contact.php">お問い合わせへ →</a></section>
</main>
<footer><a href="index.php">← トップページへ戻る</a><small>© <?= date('Y') ?> <?= e($company['company_name_en'] ?? 'PRO CHUBO HIT OKINAWA') ?></small></footer>
</body>
</html>
