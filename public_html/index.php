<?php
require __DIR__ . '/lib.php';
$heroes = published(load_content('hero'));
$homeAbout = load_content('home')[0] ?? [
    'about_kicker' => "BUILDING RESTAURANTS,\nBUILDING FUTURES.",
    'about_heading' => "厨房機器を売るだけでは、\nお店は完成しません。",
    'about_body' => "私たちは、飲食店オーナーの「こんなお店をつくりたい」という想いを形にする、店舗づくりのプロフェッショナルチームです。新規開業はもちろん、改装や設備の入れ替え、店舗の譲渡・買取まで、お店の状況やこれからの計画に寄り添いながら、最適な方法を一緒に考えます。\n\n現地調査、厨房レイアウト、CAD図面、厨房機器の選定、搬入・設置、内外装工事まで、店舗づくりに必要な工程を一つの窓口で対応します。複数の業者とのやり取りをできる限り減らし、計画全体を見渡しながら進めることで、開業準備にかかる負担や行き違いを抑えます。\n\n大切にしているのは、機器を販売することだけではなく、そのお店に本当に必要な環境をつくること。業態やメニュー、厨房の広さ、スタッフの動線、予算を丁寧に確認し、営業のしやすさや将来の設備更新まで見据えてご提案します。完成した瞬間だけでなく、その先も長く愛されるお店づくりを支えます。",
];
$homeStrength = load_content('strength')[0] ?? [
    'heading' => "飲食店経営者だから、\nできる提案がある。",
    'body' => '代表・新垣大作は、開業、運営、スタッフ育成、厨房づくり、設備投資、店舗売却までを実際に経験。現在も広島で沖縄料理店「新垣家」を経営しています。',
    'quote' => "「機械を売る」のではなく、\n「繁盛するお店づくり」を考える。",
    'images' => [],
];
$heroEffects = load_content('hero_settings')[0] ?? ['overlay' => '#102a43', 'overlay_opacity' => 35, 'dots' => true, 'dots_opacity' => 18];
$works = published(load_content('works'));
$homeWorksSettings = load_content('home_works')[0] ?? ['mode'=>'latest', 'selected_ids'=>[]];
if (($homeWorksSettings['mode'] ?? 'latest') === 'selected') {
    $workLookup = [];
    foreach ($works as $work) $workLookup[(string)($work['id'] ?? '')] = $work;
    $selectedHomeWorks = [];
    foreach ((array)($homeWorksSettings['selected_ids'] ?? []) as $workId) {
        if (isset($workLookup[$workId])) $selectedHomeWorks[] = $workLookup[$workId];
    }
    $selectedHomeIds = array_column($selectedHomeWorks, 'id');
    foreach (array_reverse($works) as $work) {
        if (count($selectedHomeWorks) >= 4) break;
        if (!in_array((string)($work['id'] ?? ''), $selectedHomeIds, true)) {
            $selectedHomeWorks[] = $work;
            $selectedHomeIds[] = (string)($work['id'] ?? '');
        }
    }
    $works = array_reverse($selectedHomeWorks);
}
$news = published(load_content('news'));
usort($news, static fn(array $a, array $b): int => strcmp((string)($b['published_at'] ?? ''), (string)($a['published_at'] ?? '')));
$company = load_content('company')[0] ?? [];
$servicePages = array_values(array_filter(load_content('services'), fn(array $item): bool => !empty($item['published'])));
$history = array_values(array_filter(array_map(function (string $line): array {
    $parts = array_map('trim', explode('|', $line, 2));
    return ['date' => $parts[0] ?? '', 'detail' => $parts[1] ?? ''];
}, preg_split('/\R/u', (string)($company['history'] ?? '')) ?: []), fn(array $row): bool => $row['date'] !== ''));
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>プロ厨房HIT沖縄｜飲食店づくりをトータルサポート</title><meta name="description" content="沖縄の飲食店開業、厨房設計、厨房機器、内外装工事、居抜き売買までワンストップで支援します。">
<link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/design-v2.css?v=3"><link rel="stylesheet" href="assets/logo-fix.css"><link rel="stylesheet" href="assets/hero-fix.css"><link rel="stylesheet" href="assets/hero-common-effects.css"><link rel="stylesheet" href="assets/slider-v2.css?v=2"><link rel="stylesheet" href="assets/service-card-link.css"><link rel="stylesheet" href="assets/home-news.css"><link rel="stylesheet" href="assets/home-work-detail.css?v=1"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"><link rel="stylesheet" href="assets/instagram-feed.css"><link rel="stylesheet" href="assets/site-width.css?v=1"><link rel="stylesheet" href="assets/home-sections.css?v=1"><script src="assets/site.js?v=6" defer></script><script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script><script src="assets/instagram-feed.js" defer></script></head><body>
<header class="site-header"><a class="brand<?= !empty($company['logo']) ? ' brand-image' : '' ?>" href="index.php"><?php if(!empty($company['logo'])):?><img src="<?=e($company['logo'])?>" alt="<?=e($company['company_name']??APP_NAME)?>"><?php else:?><span>PRO KITCHEN</span>プロ厨房HIT<small>OKINAWA</small><?php endif;?></a><button class="nav-toggle" aria-expanded="false" aria-controls="nav">MENU</button><nav id="nav"><a href="#about">私たちについて</a><a href="#services">サービス</a><a href="#works">施工事例</a><a href="#company">会社概要</a><a href="#news">最新情報</a><a class="nav-cta" href="contact.php">お問い合わせ</a></nav></header>
<main>
<section class="hero" aria-label="メインビジュアル" style="--common-overlay:<?=e($heroEffects['overlay']??'#102a43')?>;--common-opacity:<?=e(((int)($heroEffects['overlay_opacity']??35))/100)?>;--common-dot-opacity:<?=!empty($heroEffects['dots'])?e(((int)($heroEffects['dots_opacity']??18))/100):'0'?>"><div class="slides">
<?php foreach ($heroes as $i => $hero): ?><article class="slide<?= $i === 0 ? ' active' : '' ?>" style="--bg:<?= e($hero['color']) ?>;--overlay:<?= e($hero['overlay']) ?>;--opacity:<?= e(((int)$hero['overlay_opacity'])/100) ?>;--dot-opacity:<?= e(((int)$hero['dots_opacity'])/100) ?>;<?= $hero['image'] ? '--image:url('.e($hero['image']).');' : '' ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>"><div class="hero-copy"><p class="eyebrow">PRO KITCHEN HIT OKINAWA</p><h1><?= e($hero['title']) ?></h1><p><?= e($hero['lead']) ?></p><a class="button light" href="#contact">お店づくりを相談する</a></div></article><?php endforeach; ?>
</div><div class="slider-controls"><button data-prev aria-label="前のスライド">←</button><span data-counter>01 / <?= str_pad((string)count($heroes), 2, '0', STR_PAD_LEFT) ?></span><button data-next aria-label="次のスライド">→</button></div></section>
<section class="intro section" id="about"><p class="section-no">01 / ABOUT US</p><div><p class="kicker"><?= nl2br(e($homeAbout['about_kicker']??'')) ?></p><h2><?= nl2br(e($homeAbout['about_heading']??'')) ?></h2><?php foreach(preg_split('/\R{2,}/u',trim((string)($homeAbout['about_body']??'')))?:[] as $paragraph): ?><p><?= nl2br(e($paragraph)) ?></p><?php endforeach; ?></div></section>
<section class="story section strength-section" id="strength"><p class="section-no">02 / OUR STRENGTH</p><div><h2><?=nl2br(e($homeStrength['heading']??''))?></h2><?php foreach(preg_split('/\R{2,}/u',trim((string)($homeStrength['body']??'')))?:[] as $paragraph): ?><p><?=nl2br(e($paragraph))?></p><?php endforeach; ?><?php $strengthImages=array_values(array_filter((array)($homeStrength['images']??[]),'is_string'));if($strengthImages):?><div class="strength-reel" aria-label="OUR STRENGTH 写真"><div class="strength-reel-track"><?php for($copy=0;$copy<2;$copy++):?><div class="strength-reel-group" <?=$copy===1?'aria-hidden="true"':''?>><?php foreach($strengthImages as $image):?><img src="<?=e($image)?>" alt="" loading="lazy"><?php endforeach;?></div><?php endfor;?></div></div><?php endif;?><blockquote><?=nl2br(e($homeStrength['quote']??''))?></blockquote></div></section>
<section class="services section dark" id="services"><p class="section-no">03 / SERVICES</p><div><h2>お店の一生に、<br>ずっと寄り添う。</h2><div class="service-grid">
<?php foreach($servicePages as $index=>$servicePage): ?><article data-service-slug="<?=e($servicePage['id'])?>"><span><?=str_pad((string)($index+1),2,'0',STR_PAD_LEFT)?></span><h3><?=e($servicePage['title'])?></h3><p><?=e($servicePage['lead'])?></p></article><?php endforeach; ?>
</div></div></section>
<section class="section works" id="works"><p class="section-no">04 / WORKS</p><div><div class="section-head"><h2>現場から生まれた、<br>お店づくりの実績。</h2><span>OKINAWA PROJECTS</span></div><div class="work-list"><?php foreach(array_slice(array_reverse($works),0,4) as $work):$workImages=work_images($work);?><a class="home-work-detail-link" href="work-detail.php?id=<?=rawurlencode((string)$work['id'])?>"><article><?php if($workImages):?><span class="placeholder" style="background-image:url(<?=e($workImages[0])?>)"></span><?php else:?><span class="placeholder"></span><?php endif;?><p><?= e($work['category']) ?> / <?= e($work['area']) ?></p><h3><?= e($work['title']) ?></h3><strong>詳しく見る →</strong></article></a><?php endforeach; ?></div><a class="button works-more" href="works.php">地図と施工事例をもっと見る →</a></div></section>
<section class="company section" id="company"><p class="section-no">05 / COMPANY</p><div><div class="company-heading"><div><p class="eyebrow">COMPANY PROFILE</p><h2><?=e($company['company_name']??APP_NAME)?></h2><p><?=nl2br(e($company['description']??''))?></p></div><p class="company-en"><?=e($company['company_name_en']??'PRO CHUBO HIT OKINAWA')?></p></div><div class="company-columns"><dl><div><dt>所在地</dt><dd><?=e(trim(($company['postal_code']??'').' '.($company['address']??'')))?></dd></div><div><dt>代表取締役</dt><dd><?=e($company['representative']??'')?></dd></div><?php if(!empty($company['phone'])):?><div><dt>電話</dt><dd><?=e($company['phone'])?></dd></div><?php endif;?></dl><div class="history"><h3>HISTORY <small>沿革</small></h3><?php foreach($history as $row):?><article><time><?=e($row['date'])?></time><p><?=e($row['detail'])?></p></article><?php endforeach;?></div></div></div></section>
<section class="news section" id="news"><p class="section-no">06 / NEWS</p><div><h2>最新情報</h2><?php foreach(array_slice($news,0,3) as $item): ?><a class="home-news-link" href="news-detail.php?id=<?= rawurlencode((string)($item['id']??'')) ?>"><article><time><?= e($item['published_at']) ?></time><span><?= e($item['category']) ?></span><h3><?= e($item['title']) ?></h3></article></a><?php endforeach; ?><a class="button news-more" href="news.php">最新情報をもっと見る →</a></div></section>
<section class="instagram-section section" id="instagram"><p class="section-no">07 / INSTAGRAM</p><div><div class="instagram-heading"><div class="instagram-account"><?php if(!empty($company['logo'])):?><img src="<?=e($company['logo'])?>" class="instagram-account-thumb" id="profile-okinawa" alt="プロ厨房HIT 沖縄営業所"><?php else:?><span class="instagram-account-thumb" id="profile-okinawa"></span><?php endif;?><div><small>OFFICIAL INSTAGRAM</small><h2>プロ厨房HIT 沖縄営業所</h2></div></div></div><div class="swiper instagram-swiper swiper-okinawa"><div class="swiper-wrapper" id="feed-okinawa"><p class="instagram-loading">投稿を読み込んでいます。</p></div><button class="swiper-button-prev swiper-button-okinawa-prev" type="button" aria-label="前の投稿"></button><button class="swiper-button-next swiper-button-okinawa-next" type="button" aria-label="次の投稿"></button></div></div></section>
<section class="contact" id="contact"><p class="eyebrow">LET'S BUILD YOUR RESTAURANT.</p><h2>そのお店の未来を、<br>一緒につくりませんか。</h2><p>開業、厨房機器、工事、店舗売却。まだ構想段階でもお気軽にご相談ください。</p><a class="button light" href="contact.php">お問い合わせフォームへ</a></section>
</main><footer class="site-footer"><?php if(!empty($company['logo'])):?><img class="footer-logo" src="<?=e($company['logo'])?>" alt="<?=e($company['company_name'])?>"><?php else:?><div class="brand"><span>PRO KITCHEN</span>プロ厨房HIT<small>OKINAWA</small></div><?php endif;?><small>© <?= date('Y') ?> <?=e($company['company_name_en']??'PRO CHUBO HIT OKINAWA')?></small></footer>
</body></html>
