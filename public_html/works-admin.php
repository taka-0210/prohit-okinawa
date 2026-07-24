<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');

$items = load_content('works');
$maps = load_content('maps');
usort($maps, fn(array $a, array $b): int => [(int)($a['sort_order']??0),(int)($a['map_number']??0)] <=> [(int)($b['sort_order']??0),(int)($b['map_number']??0)]);
$mapLookup = [];
foreach ($maps as $map) $mapLookup[$map['id']] = $map;
$error = '';
$editId = (string)($_GET['edit'] ?? '');
$edit = null;
foreach ($items as $item) {
    if (($item['id'] ?? '') === $editId) $edit = $item;
}
function work_external_url(string $value): string
{
    $value = trim($value);
    if ($value === '') return '';
    if (!preg_match('~^https?://~i', $value)) $value = 'https://' . $value;
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('外部リンクURLを正しい形式で入力してください。');
    }
    $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        throw new RuntimeException('URLはhttp://またはhttps://で入力してください。');
    }
    return $value;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $action = (string)($_POST['action'] ?? 'save');
        $id = (string)($_POST['id'] ?? '');
        if ($action === 'delete') {
            $items = array_values(array_filter($items, fn(array $item): bool => ($item['id'] ?? '') !== $id));
            save_content('works', $items);
            redirect('works-admin.php?saved=1');
        }
        $existing = null;
        foreach ($items as $item) {
            if (($item['id'] ?? '') === $id) $existing = $item;
        }
        $id = $id !== '' ? $id : 'work-' . bin2hex(random_bytes(5));
        $kept = array_values(array_filter(
            (array)($_POST['keep_images'] ?? []),
            fn($path) => is_string($path) && str_starts_with($path, 'uploads/') && is_file(__DIR__ . '/' . $path)
        ));
        $newImages = upload_image_files('images', max(0, 10 - count($kept)));
        $images = array_slice(array_merge($kept, $newImages), 0, 10);
        $mapId = (string)($_POST['map_id'] ?? 'outside');
        if ($mapId !== 'outside' && !isset($mapLookup[$mapId])) {
            throw new RuntimeException('登録済みの地図を選択してください。');
        }
        $positionX = max(0, min(100, (float)($_POST['position_x'] ?? 50)));
        $positionY = max(0, min(100, (float)($_POST['position_y'] ?? 50)));
        $record = [
            'id' => $id,
            'title' => trim((string)($_POST['title'] ?? '')),
            'category' => trim((string)($_POST['category'] ?? '')),
            'area' => trim((string)($_POST['area'] ?? '')),
            'designer' => trim((string)($_POST['designer'] ?? '')),
            'address' => trim((string)($_POST['address'] ?? '')),
            'google_maps_url' => work_external_url((string)($_POST['google_maps_url'] ?? '')),
            'summary' => trim((string)($_POST['summary'] ?? '')),
            'instagram_url' => work_external_url((string)($_POST['instagram_url'] ?? '')),
            'website_url' => work_external_url((string)($_POST['website_url'] ?? '')),
            'map_id' => $mapId,
            'position_x' => $positionX,
            'position_y' => $positionY,
            'images' => $images,
            'image' => $images[0] ?? '',
            'published' => isset($_POST['published']),
        ];
        if ($record['title'] === '') throw new RuntimeException('タイトルは必須です。');
        $updated = false;
        foreach ($items as $key => $item) {
            if (($item['id'] ?? '') === $id) {
                $items[$key] = $record;
                $updated = true;
            }
        }
        if (!$updated) $items[] = $record;
        save_content('works', $items);
        redirect('works-admin.php?edit=' . rawurlencode($id) . '&saved=1');
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
$currentImages = $edit ? work_images($edit) : [];
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>施工事例管理</title>
  <link rel="stylesheet" href="assets/admin.css">
  <link rel="stylesheet" href="assets/admin-menu-fix.css">
  <link rel="stylesheet" href="assets/works-admin.css?v=2">
</head>
<body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav></nav></aside>
<script src="assets/admin-nav.js?v=5" defer></script>
<main class="admin-main">
  <header><div><p>PRO CHUBO HIT OKINAWA</p><h1>施工事例</h1></div><a href="works.php" target="_blank">施工事例ページを確認 ↗</a></header>
  <?php if(isset($_GET['saved'])): ?><p class="success">保存しました。</p><?php endif; ?>
  <?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <?php if(!$maps): ?><p class="error">沖縄県内の事例を登録する場合は、先に「地図管理」で地図を登録してください。</p><?php endif; ?>
  <div class="toolbar"><p><?= count($items) ?>件登録</p><a class="primary" href="works-admin.php?edit=new">＋ 新規追加</a></div>
  <div class="works-admin-layout">
    <section class="panel list">
      <table><thead><tr><th>施工事例</th><th>地図</th><th>画像</th><th>状態</th><th></th></tr></thead>
      <tbody>
      <?php foreach($items as $item): $images=work_images($item); $map=$mapLookup[$item['map_id']??'']??null; ?>
        <tr>
          <td><strong><?= e($item['title']??'') ?></strong><small><?= e($item['area']??'') ?></small></td>
          <td><?= $map?'MAP '.str_pad((string)($map['map_number']??0),2,'0',STR_PAD_LEFT).' '.e($map['title']??''):'沖縄以外' ?></td>
          <td><?= count($images) ?> / 10</td>
          <td><span class="status <?= !empty($item['published'])?'live':'' ?>"><?= !empty($item['published'])?'公開':'下書き' ?></span></td>
          <td><a href="works-admin.php?edit=<?= e($item['id']) ?>">編集</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table>
    </section>
    <?php if($editId): $v=$edit??[]; $selectedMapId=(string)($v['map_id']??'outside'); ?>
    <section class="panel editor">
      <h2><?= $edit?'編集':'新規追加' ?></h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e($edit['id']??'') ?>">
        <label>タイトル<input name="title" required value="<?= e($v['title']??'') ?>"></label>
        <div class="fields">
          <label>カテゴリ<input name="category" value="<?= e($v['category']??'') ?>"></label>
          <label>市町村・表示地域<input name="area" value="<?= e($v['area']??'') ?>"></label>
        </div>
        <div class="fields">
          <label>デザイナー<input name="designer" value="<?= e($v['designer']??'') ?>"></label>
          <label>住所<input name="address" value="<?= e($v['address']??'') ?>" placeholder="沖縄県〇〇市..."></label>
        </div>
        <label>GoogleマップURL<input type="url" name="google_maps_url" value="<?= e($v['google_maps_url']??'') ?>" placeholder="https://maps.app.goo.gl/..."></label>
        <p class="hint">Googleマップの「共有」からコピーしたURLを登録してください。未入力の場合は住所検索を使用します。</p>
        <label>概要<textarea name="summary" rows="5"><?= e($v['summary']??'') ?></textarea></label>
        <div class="fields">
          <label>Instagram URL<input type="url" name="instagram_url" value="<?= e($v['instagram_url']??'') ?>" placeholder="https://www.instagram.com/..."></label>
          <label>ホームページURL<input type="url" name="website_url" value="<?= e($v['website_url']??'') ?>" placeholder="https://example.com/"></label>
        </div>
        <p class="hint">登録したリンクは、公開施工事例ページに表示されます。未入力の場合は表示されません。</p>
        <div class="map-fields" data-map-fields>
          <label>地図
            <select name="map_id" data-map-select>
              <option value="outside" <?= $selectedMapId==='outside'?'selected':'' ?>>地図なし／未割り当て</option>
              <?php foreach($maps as $map): ?>
              <option value="<?= e($map['id']) ?>" data-image="<?= e($map['image']??'') ?>" data-scale="<?= e(((int)($map['display_scale']??100))/100) ?>" <?= $selectedMapId===($map['id']??'')?'selected':'' ?>>MAP <?= str_pad((string)($map['map_number']??0),2,'0',STR_PAD_LEFT) ?>｜<?= e($map['title']??'') ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <div class="fields map-axis-fields">
            <label>横軸（0〜100）<input type="number" min="0" max="100" step="0.1" name="position_x" data-position-x value="<?= e($v['position_x']??50) ?>"></label>
            <label>縦軸（0〜100）<input type="number" min="0" max="100" step="0.1" name="position_y" data-position-y value="<?= e($v['position_y']??50) ?>"></label>
          </div>
          <p class="hint">地図の左上が「横軸0・縦軸0」、右下が「横軸100・縦軸100」です。地図上をクリックして位置を指定できます。</p>
          <div class="position-map" data-position-map hidden><img alt="選択中の地図"><span></span></div>
        </div>
        <div class="gallery-admin">
          <div class="gallery-heading"><h3>施工写真</h3><span><?= count($currentImages) ?> / 10枚</span></div>
          <?php if($currentImages): ?>
          <div class="existing-images">
            <?php foreach($currentImages as $index=>$image): ?>
            <label><img src="<?= e($image) ?>" alt="登録画像<?= $index+1 ?>"><span><input type="checkbox" name="keep_images[]" value="<?= e($image) ?>" checked> 使用する</span><small><?= $index===0?'メイン画像':'画像 '.($index+1) ?></small></label>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <label class="upload-zone">画像を追加<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple><small>合計最大10枚／1枚6MBまで。先頭画像がメイン画像になります。<br>※長辺1920pxを超える画像は、比率を保って自動縮小します。</small></label>
        </div>
        <label class="check"><input type="checkbox" name="published" <?= !isset($v['published'])||$v['published']?'checked':'' ?>>公開する</label>
        <button class="primary">保存する</button>
      </form>
      <?php if($edit): ?>
      <form method="post" onsubmit="return confirm('施工事例を削除しますか？')">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
        <button class="danger">削除</button>
      </form>
      <?php endif; ?>
    </section>
    <?php endif; ?>
  </div>
</main>
<script src="assets/works-admin-map.js?v=3" defer></script>
</body>
</html>
