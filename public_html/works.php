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
            if ($threshold > 0 && $distance <= $threshold && $distance < $nearestDistance) {
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
  <link rel="stylesheet" href="assets/works-page.css?v=2">
  <link rel="stylesheet" href="assets/works-links.css?v=6">
  <link rel="stylesheet" href="assets/map-scale.css?v=8">
  <link rel="stylesheet" href="assets/site-width.css?v=1">
  <link rel="stylesheet" href="assets/works-cards.css?v=4">
  <script src="assets/works-page.js?v=9" defer></script>
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
        <div class="map-viewport" data-map-viewport>
          <div class="map-canvas" style="--map-scale:<?= e($mapScale) ?>">
            <img src="<?= e($map['image']??'') ?>" alt="<?= e($map['title']??'') ?>の施工事例地図">
            <?php foreach(cluster_map_works($group['works'],max(0,min(20,(float)($map['cluster_threshold']??6)))) as $cluster): $clusterCount=count($cluster['items']); $clusterIds=array_map(fn(array $item): string => (string)($item['work']['id']??''),$cluster['items']); ?>
            <div class="pin-group<?= $clusterCount>1?' is-cluster':'' ?>" style="left:<?= e(50+($cluster['x']-50)*$mapScale) ?>%;top:<?= e($cluster['y']) ?>%" data-cluster-ids="<?= e(json_encode($clusterIds,JSON_UNESCAPED_SLASHES)) ?>">
              <?php if($clusterCount===1): $item=$cluster['items'][0]; ?>
              <button type="button" class="project-pin single-pin" data-work-pin="<?= e($item['work']['id']) ?>" data-single-toggle aria-expanded="false" aria-label="<?= e($item['work']['title']??'') ?>を表示"></button>
              <div class="pin-popup" data-pin-popup hidden>
                <strong>この場所の施工事例</strong>
                <a href="work-detail.php?id=<?= rawurlencode((string)$item['work']['id']) ?>" data-popup-work="<?= e($item['work']['id']) ?>"><span class="single-popup-bullet">・</span><?= e($item['work']['title']??'') ?></a>
              </div>
              <?php else: ?>
              <button type="button" class="project-pin cluster-pin" data-cluster-toggle aria-expanded="false" aria-label="このエリアの<?= $clusterCount ?>件を表示"><span><?= $clusterCount ?></span></button>
              <div class="pin-popup" data-pin-popup hidden>
                <strong>このエリアの施工事例</strong>
                <?php foreach($cluster['items'] as $item): ?>
                <a href="work-detail.php?id=<?= rawurlencode((string)$item['work']['id']) ?>" data-popup-work="<?= e($item['work']['id']) ?>"><span class="single-popup-bullet">・</span><?= e($item['work']['title']??'') ?></a>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <p>地図上のマークを選ぶと、該当する施工事例を確認できます。数字入りのマークは近接する店舗の件数を表しています。<span class="map-gesture-hint"><span class="map-hint-desktop">地図上でマウスホイールを動かすと拡大・縮小できます。</span><span class="map-hint-mobile">地図は2本指で拡大・縮小できます。</span></span></p>
      </div>
      <?php endif; ?>
      <div class="project-list">
        <?php if(!$group['works']): ?><p class="empty">現在、公開中の施工事例はありません。</p><?php endif; ?>
        <?php foreach($group['works'] as $index=>$work): $images=work_images($work); ?>
        <a class="project-card-link" href="work-detail.php?id=<?= rawurlencode((string)$work['id']) ?>">
          <article id="work-<?= e($work['id']) ?>" data-work-card="<?= e($work['id']) ?>">
            <?php if($images): ?><span class="project-photo" style="background-image:url(<?= e($images[0]) ?>)"></span>
            <?php else: ?><span class="project-photo is-placeholder"></span><?php endif; ?>
            <div class="project-copy">
              <p><?= e($work['category']??'') ?><?= !empty($work['area'])?' / '.e($work['area']):'' ?></p>
              <h2><?= e($work['title']??'') ?></h2>
              <?php if(!empty($work['designer'])): ?><small>DESIGNER　<?= e($work['designer']) ?></small><?php endif; ?>
              <strong>詳しく見る <span>→</span></strong>
            </div>
          </article>
        </a>
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
