<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');

$items = load_content('maps');
$works = load_content('works');
$error = '';
$editId = (string)($_GET['edit'] ?? '');
$edit = null;
foreach ($items as $item) {
    if (($item['id'] ?? '') === $editId) $edit = $item;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $action = (string)($_POST['action'] ?? 'save');
        $id = (string)($_POST['id'] ?? '');
        if ($action === 'delete') {
            foreach ($works as $work) {
                if (($work['map_id'] ?? '') === $id) {
                    throw new RuntimeException('この地図を使用している施工事例があります。先に施工事例の地図を変更してください。');
                }
            }
            $items = array_values(array_filter($items, fn(array $item): bool => ($item['id'] ?? '') !== $id));
            save_content('maps', $items);
            redirect('maps-admin.php?saved=1');
        }

        $existing = null;
        foreach ($items as $item) {
            if (($item['id'] ?? '') === $id) $existing = $item;
        }
        if ($id === '') {
            $id = 'map-' . bin2hex(random_bytes(5));
            $numbers = array_map(fn(array $item): int => (int)($item['map_number'] ?? 0), $items);
            $mapNumber = ($numbers ? max($numbers) : 0) + 1;
        } else {
            $mapNumber = (int)($existing['map_number'] ?? 0);
        }
        $record = [
            'id' => $id,
            'map_number' => $mapNumber,
            'title' => trim((string)($_POST['title'] ?? '')),
            'image' => upload_image('image', (string)($existing['image'] ?? '')),
            'display_scale' => max(40, min(150, (int)($_POST['display_scale'] ?? 100))),
            'cluster_threshold' => max(0, min(20, (float)($_POST['cluster_threshold'] ?? 6))),
            'sort_order' => (int)($_POST['sort_order'] ?? $mapNumber),
            'published' => isset($_POST['published']),
        ];
        if ($record['title'] === '') throw new RuntimeException('地図名は必須です。');
        if ($record['image'] === '') throw new RuntimeException('地図画像を登録してください。');

        $updated = false;
        foreach ($items as $key => $item) {
            if (($item['id'] ?? '') === $id) {
                $items[$key] = $record;
                $updated = true;
            }
        }
        if (!$updated) $items[] = $record;
        save_content('maps', $items);
        redirect('maps-admin.php?edit=' . rawurlencode($id) . '&saved=1');
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

usort($items, fn(array $a, array $b): int => [(int)($a['sort_order'] ?? 0), (int)($a['map_number'] ?? 0)] <=> [(int)($b['sort_order'] ?? 0), (int)($b['map_number'] ?? 0)]);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>地図管理</title>
  <link rel="stylesheet" href="assets/admin.css">
  <link rel="stylesheet" href="assets/admin-menu-fix.css">
  <link rel="stylesheet" href="assets/maps-admin.css?v=3">
</head>
<body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav></nav></aside>
<script src="assets/admin-nav.js?v=5" defer></script>
<main class="admin-main">
  <header><div><p>PRO CHUBO HIT OKINAWA</p><h1>地図管理</h1></div><a href="works.php" target="_blank">施工事例ページを確認 ↗</a></header>
  <?php if(isset($_GET['saved'])): ?><p class="success">保存しました。</p><?php endif; ?>
  <?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <div class="toolbar"><p><?= count($items) ?>件登録</p><a class="primary" href="maps-admin.php?edit=new">＋ 新規追加</a></div>
  <div class="maps-admin-layout">
    <section class="panel list">
      <table><thead><tr><th>地図番号</th><th>地図名</th><th>使用事例</th><th>状態</th><th></th></tr></thead>
      <tbody>
      <?php foreach($items as $item): $usageCount=count(array_filter($works, fn(array $work): bool => ($work['map_id']??'')===($item['id']??''))); ?>
        <tr>
          <td>MAP <?= str_pad((string)($item['map_number']??0), 2, '0', STR_PAD_LEFT) ?></td>
          <td><strong><?= e($item['title']??'') ?></strong></td>
          <td><?= $usageCount ?>件</td>
          <td><span class="status <?= !empty($item['published'])?'live':'' ?>"><?= !empty($item['published'])?'公開':'下書き' ?></span></td>
          <td><a href="maps-admin.php?edit=<?= e($item['id']) ?>">編集</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table>
    </section>
    <?php if($editId): $v=$edit??[]; ?>
    <section class="panel editor">
      <h2><?= $edit?'編集':'新規追加' ?></h2>
      <?php if($edit): ?><p class="map-number">MAP <?= str_pad((string)($edit['map_number']??0), 2, '0', STR_PAD_LEFT) ?></p><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e($edit['id']??'') ?>">
        <label>地図名<input name="title" required value="<?= e($v['title']??'') ?>" placeholder="例：沖縄本島"></label>
        <label>表示倍率（％）<input type="number" name="display_scale" min="40" max="150" step="1" value="<?= e($v['display_scale']??100) ?>"><small>100％が元の大きさです。40～150％の範囲で調整できます。</small></label>
        <label>マーカーをまとめる距離（％）<input type="number" name="cluster_threshold" min="0" max="20" step="0.5" value="<?= e($v['cluster_threshold']??6) ?>"><small>縦軸・横軸の座標が近い施工事例をまとめます。初期値は6％、0％にするとまとめません。</small></label>
        <label>表示順<input type="number" name="sort_order" min="0" value="<?= e($v['sort_order']??count($items)+1) ?>"></label>
        <?php if(!empty($v['image'])): ?><div class="map-preview" style="--map-scale:<?= e(((int)($v['display_scale']??100))/100) ?>"><img src="<?= e($v['image']) ?>" alt="登録中の地図"></div><?php endif; ?>
        <label>地図画像<input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?= $edit?'':'required' ?>><small>JPEG・PNG・WebP／6MBまで。<br>※長辺1920pxを超える画像は、比率を保って自動縮小します。</small></label>
        <label class="check"><input type="checkbox" name="published" <?= !isset($v['published'])||$v['published']?'checked':'' ?>>公開する</label>
        <button class="primary">保存する</button>
      </form>
      <?php if($edit): ?>
      <form method="post" onsubmit="return confirm('この地図を削除しますか？')">
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
</body>
</html>
