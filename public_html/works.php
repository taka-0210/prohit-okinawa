<?php
require __DIR__ . '/lib.php';
$company = load_content('company')[0] ?? [];
$maps = array_values(array_filter(load_content('maps'), fn(array $map): bool => !empty($map['published'])));
usort($maps, fn(array $a, array $b): int => [(int)($a['sort_order']??0),(int)($a['map_number']??0)] <=> [(int)($b['sort_order']??0),(int)($b['map_number']??0)]);
$works = array_values(array_filter(load_content('works'), fn(array $work): bool => !empty($work['published'])));
$groups = [];
foreach ($maps as $map) {
    $groups[$map['id']] = ['map'=>$map,'works'=>[]];
}
$outsideWorks = [];
foreach ($works as $work) {
    $mapId = (string)($work['map_id'] ?? 'outside');
    if ($mapId !== 'outside' && isset($groups[$mapId])) {
        $groups[$mapId]['works'][] = $work;
    } else {
        $outsideWorks[] = $work;
    }
}
$groups['outside'] = ['map'=>null,'works'=>$outsideWorks];
$activeGroup = array_key_first(array_filter($groups, fn(array $group): bool => $group['works'] !== [])) ?? array_key_first($groups);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>施工事例｜<?= e($company['company_name']??APP_NAME) ?></title>
  <meta name="description" content="沖縄県内・県外で手がけた厨房、店舗工事、飲食店開業支援の施工事例をご紹介します。">
  <link rel="stylesheet" href="assets/works-page.css?v=1">
  <link rel="stylesheet" href="assets/work-gallery.css">
  <script src="assets/works-page.js?v=1" defer></script>
  <script src="assets/work-gallery.js" defer></script>
</head>
<body>
<header class="works-header">
  <a class="works-brand" href="index.php">
    <?php if(!empty($company['logo'])): ?><img src="<?= e($company['logo']) ?>" alt="<?= e($company['company_name']??APP_NAME) ?>">
    <?php else: ?><strong><?= e(APP_NAME) ?></strong><?php endif; ?>
  </a>
  <nav><a href="index.php#services">サービス</a><a href="index.php#company">会社概要</a><a class="contact-link" href="contact.php">お問い合わせ</a></nav>
</header>
<main>
  <section class="works-hero"><p>WORKS / PROJECT MAP</p><h1>施工事例</h1><span>現場から生まれた、<br>お店づくりの実績。</span></section>
  <section class="works-browser">
    <div class="works-tabs" role="tablist" aria-label="表示する地図">
      <?php foreach($groups as $groupId=>$group): $map=$group['map']; ?>
      <button type="button" role="tab" data-map-tab="<?= e($groupId) ?>" aria-selected="<?= $groupId===$activeGroup?'true':'false' ?>">
        <?php if($map): ?><small>MAP <?= str_pad((string)($map['map_number']??0),2,'0',STR_PAD_LEFT) ?></small><?= e($map['title']??'') ?>
        <?php else: ?><small>OTHER AREA</small>沖縄以外<?php endif; ?>
        <span><?= count($group['works']) ?>件</span>
      </button>
      <?php endforeach; ?>
    </div>
    <label class="works-select-label">表示エリア
      <select data-map-select>
        <?php foreach($groups as $groupId=>$group): $map=$group['map']; ?>
        <option value="<?= e($groupId) ?>" <?= $groupId===$activeGroup?'selected':'' ?>><?= $map?'MAP '.str_pad((string)($map['map_number']??0),2,'0',STR_PAD_LEFT).'｜'.($map['title']??''):'沖縄以外' ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <?php foreach($groups as $groupId=>$group): $map=$group['map']; ?>
    <div class="works-panel<?= $map?'':' no-map' ?>" data-map-panel="<?= e($groupId) ?>" <?= $groupId===$activeGroup?'':'hidden' ?>>
      <?php if($map): ?>
      <div class="project-map">
        <div class="map-canvas">
          <img src="<?= e($map['image']??'') ?>" alt="<?= e($map['title']??'') ?>の施工事例地図">
          <?php foreach($group['works'] as $index=>$work): ?>
          <button type="button" class="project-pin" data-work-pin="<?= e($work['id']) ?>" style="left:<?= e(max(0,min(100,(float)($work['position_x']??50)))) ?>%;top:<?= e(max(0,min(100,(float)($work['position_y']??50)))) ?>%" aria-label="<?= e($work['title']??'') ?>"><span><?= $index+1 ?></span></button>
          <?php endforeach; ?>
        </div>
        <p>地図上の番号を選ぶと、該当する施工事例を確認できます。</p>
      </div>
      <?php endif; ?>
      <div class="project-list">
        <?php if(!$group['works']): ?><p class="empty">現在、公開中の施工事例はありません。</p><?php endif; ?>
        <?php foreach($group['works'] as $index=>$work): $images=work_images($work); ?>
        <article id="work-<?= e($work['id']) ?>" data-work-card="<?= e($work['id']) ?>">
          <?php if($images): ?>
          <button class="work-gallery-trigger" type="button" data-title="<?= e($work['title']??'') ?>" data-images="<?= e(json_encode($images,JSON_UNESCAPED_SLASHES)) ?>">
            <span class="project-photo" style="background-image:url(<?= e($images[0]) ?>)"></span>
            <?php if(count($images)>1): ?><span class="photo-count"><?= count($images) ?> PHOTOS</span><?php endif; ?>
          </button>
          <?php else: ?><div class="project-photo is-placeholder"></div><?php endif; ?>
          <div class="project-copy">
            <p><span><?= $index+1 ?></span><?= e($work['category']??'') ?><?= !empty($work['area'])?' / '.e($work['area']):'' ?></p>
            <h2><?= e($work['title']??'') ?></h2>
            <div><?= nl2br(e($work['summary']??'')) ?></div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </section>
  <section class="works-cta"><p>START YOUR RESTAURANT WITH US.</p><h2>次のお店づくりを、<br>一緒に始めませんか。</h2><a href="contact.php">お問い合わせへ →</a></section>
</main>
<footer><a href="index.php">トップページへ戻る</a><small>© <?= date('Y') ?> <?= e($company['company_name_en']??'PRO CHUBO HIT OKINAWA') ?></small></footer>
</body>
</html>
