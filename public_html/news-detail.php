<?php
require __DIR__ . '/lib.php';

$company = load_content('company')[0] ?? [];
$id = (string)($_GET['id'] ?? '');
$article = null;
foreach (published(load_content('news')) as $item) {
    if (($item['id'] ?? '') === $id) {
        $article = $item;
        break;
    }
}
if (!$article) {
    http_response_code(404);
    exit('記事が見つかりません。');
}
$blocks = $article['blocks'] ?? [];
if ($blocks === [] && !empty($article['body'])) {
    $blocks = [['type'=>'text', 'text'=>(string)$article['body']]];
}
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($article['title'])?>｜<?=e($company['company_name']??APP_NAME)?></title><meta name="description" content="<?=e(mb_substr((string)($article['body']??''),0,120))?>"><link rel="stylesheet" href="assets/news-page.css"><link rel="stylesheet" href="assets/news-detail.css"></head>
<body><header class="news-header"><a class="news-brand" href="index.php"><?php if(!empty($company['logo'])):?><img src="<?=e($company['logo'])?>" alt="<?=e($company['company_name']??APP_NAME)?>"><?php else:?><strong><?=e(APP_NAME)?></strong><?php endif;?></a><nav><a href="news.php">最新情報一覧</a><a href="works.php">施工事例</a><a class="contact-link" href="contact.php">お問い合わせ</a></nav></header>
<main><article class="news-detail"><header><div><time datetime="<?=e($article['published_at']??'')?>"><?=e($article['published_at']??'')?></time><span><?=e($article['category']??'お知らせ')?></span></div><h1><?=e($article['title'])?></h1></header><div class="news-detail-body">
<?php foreach($blocks as $block): ?>
<?php if(($block['type']??'text')==='image'&&!empty($block['image'])):?><figure><img src="<?=e($block['image'])?>" alt=""></figure>
<?php elseif(($block['type']??'text')==='text'&&!empty($block['text'])):?><div class="article-text"><?=nl2br(e($block['text']))?></div><?php endif;?>
<?php endforeach; ?>
</div><a class="news-back" href="news.php">← 最新情報一覧へ戻る</a></article>
<section class="news-cta"><p>START YOUR RESTAURANT WITH US.</p><h2>お店づくりのことなら、<br>お気軽にご相談ください。</h2><a href="contact.php">お問い合わせへ →</a></section></main>
<footer><a href="index.php">← トップページへ戻る</a><small>© <?=date('Y')?> <?=e($company['company_name_en']??'PRO CHUBO HIT OKINAWA')?></small></footer></body></html>
