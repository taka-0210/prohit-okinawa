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
if ($outsideWorks !== []) {
    $groups['outside'] = ['map'=>null,'works'=>$outsideWorks];
}
$activeGroup = array_key_first(array_filter($groups, fn(array $group): bool => $group['works'] !== [])) ?? array_key_first($groups);
function cluster_map_works(array $works, float $threshold = 6.0): array
{
    $clusters = [];
    foreach ($works as $index => $work) {
        $x = max(0, min(100, (float)($work['position_x'] ?? 50)));
        $y = max(0, min(100, (float)($work['position_y'] ?? 50)));
        $nearest = null;
        $nearestDistance = INF;
        foreach ($clusters as $clusterIndex => $cluster) {
            $distance = hypot($x - $cluster['x'], $y - $cluster['y']);
            if ($distance <= $threshold && $distance < $nearestDistance) {
                $nearest = $clusterIndex;
                $nearestDistance = $distance;
            }
        }
        $entry = ['work'=>$work,'number'=>$index+1];
        if ($nearest === null) {
            $clusters[] = ['x'=>$x,'y'=>$y,'items'=>[$entry]];
            continue;
        }
        $count = count($clusters[$nearest]['items']);
        $clusters[$nearest]['x'] = (($clusters[$nearest]['x'] * $count) + $x) / ($count + 1);
        $clusters[$nearest]['y'] = (($clusters[$nearest]['y'] * $count) + $y) / ($count + 1);
        $clusters[$nearest]['items'][] = $entry;
    }
    return $clusters;
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>施工事例｜<?= e($company['company_name']??APP_NAME) ?></title>
  <meta name="description" content="沖縄県内・県外で手がけた厨房、店舗工事、飲食店開業支援の施工事例をご紹介します。">
  <link rel="stylesheet" href="assets/works-page.css?v=1">
  <link rel="stylesheet" href="assets/works-links.css?v=3">
  <link rel="stylesheet" href="assets/work-gallery.css">
  <link rel="stylesheet" href="assets/map-scale.css?v=1">
  <script src="assets/works-page.js?v=2" defer></script>
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
      <?php $mapScale=max(.4,min(1.5,((int)($map['display_scale']??100))/100)); ?>
      <div class="project-map">
        <div class="map-canvas" style="--map-scale:<?= e($mapScale) ?>">
          <img src="<?= e($map['image']??'') ?>" alt="<?= e($map['title']??'') ?>の施工事例地図">
          <?php foreach(cluster_map_works($group['works']) as $cluster): $clusterCount=count($cluster['items']); $clusterIds=array_map(fn(array $item): string => (string)($item['work']['id']??''),$cluster['items']); ?>
          <div class="pin-group" style="left:<?= e(50+($cluster['x']-50)*$mapScale) ?>%;top:<?= e(50+($cluster['y']-50)*$mapScale) ?>%" data-cluster-ids="<?= e(json_encode($clusterIds,JSON_UNESCAPED_SLASHES)) ?>">
            <?php if($clusterCount===1): $item=$cluster['items'][0]; ?>
            <button type="button" class="project-pin" data-work-pin="<?= e($item['work']['id']) ?>" aria-label="<?= e($item['work']['title']??'') ?>"><span><?= $item['number'] ?></span></button>
            <?php else: ?>
            <button type="button" class="project-pin cluster-pin" data-cluster-toggle aria-expanded="false" aria-label="このエリアの<?= $clusterCount ?>件を表示"><span><?= $clusterCount ?></span></button>
            <div class="pin-popup" data-pin-popup hidden>
              <strong>このエリアの施工事例</strong>
              <?php foreach($cluster['items'] as $item): ?>
              <button type="button" data-popup-work="<?= e($item['work']['id']) ?>"><span><?= $item['number'] ?></span><?= e($item['work']['title']??'') ?></button>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <p>地図上の番号を選ぶと、該当する施工事例を確認できます。数字が店舗数のマークは、近接する事例をまとめて表示しています。</p>
      </div>
      <?php endif; ?>
      <div class="project-list">
        <?php if(!$group['works']): ?><p class="empty">現在、公開中の施工事例はありません。</p><?php endif; ?>
        <?php foreach($group['works'] as $index=>$work): $images=work_images($work); $googleMapsUrl=(string)($work['google_maps_url']??''); if($googleMapsUrl===''&&!empty($work['address']))$googleMapsUrl='https://www.google.com/maps/search/?api=1&query='.rawurlencode((string)$work['address']); ?>
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
            <?php if(!empty($work['designer'])||!empty($work['address'])||$googleMapsUrl!==''): ?>
            <dl class="project-meta">
              <?php if(!empty($work['designer'])): ?><div><dt>DESIGNER</dt><dd><?= e($work['designer']) ?></dd></div><?php endif; ?>
              <?php if($googleMapsUrl!==''): ?><div><dt>ADDRESS</dt><dd><a href="<?= e($googleMapsUrl) ?>" target="_blank" rel="noopener noreferrer"><?= !empty($work['address'])?e($work['address']):'Googleマップを開く' ?> ↗</a></dd></div><?php elseif(!empty($work['address'])): ?><div><dt>ADDRESS</dt><dd><?= e($work['address']) ?></dd></div><?php endif; ?>
            </dl>
            <?php endif; ?>
            <div><?= nl2br(e($work['summary']??'')) ?></div>
            <?php if(!empty($work['instagram_url'])||!empty($work['website_url'])): ?>
            <div class="project-links">
              <?php if(!empty($work['instagram_url'])): ?><a href="<?= e($work['instagram_url']) ?>" target="_blank" rel="noopener noreferrer">Instagram ↗</a><?php endif; ?>
              <?php if(!empty($work['website_url'])): ?><a href="<?= e($work['website_url']) ?>" target="_blank" rel="noopener noreferrer">ホームページ ↗</a><?php endif; ?>
            </div>
            <?php endif; ?>
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
