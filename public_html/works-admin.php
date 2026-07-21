<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');
$items = load_content('works'); $error = ''; $editId = (string)($_GET['edit'] ?? ''); $edit = null;
foreach ($items as $item) if (($item['id'] ?? '') === $editId) $edit = $item;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $action = (string)($_POST['action'] ?? 'save'); $id = (string)($_POST['id'] ?? '');
        if ($action === 'delete') {
            $items = array_values(array_filter($items, fn(array $item): bool => ($item['id'] ?? '') !== $id));
            save_content('works', $items); redirect('works-admin.php?saved=1');
        }
        $existing = null; foreach ($items as $item) if (($item['id'] ?? '') === $id) $existing = $item;
        $id = $id !== '' ? $id : 'work-' . bin2hex(random_bytes(5));
        $kept = array_values(array_filter((array)($_POST['keep_images'] ?? []), fn($path) => is_string($path) && str_starts_with($path, 'uploads/') && is_file(__DIR__ . '/' . $path)));
        $newImages = upload_image_files('images', max(0, 10 - count($kept)));
        $images = array_slice(array_merge($kept, $newImages), 0, 10);
        $record = ['id'=>$id,'title'=>trim((string)($_POST['title']??'')),'category'=>trim((string)($_POST['category']??'')),'region'=>trim((string)($_POST['region']??'')),'area'=>trim((string)($_POST['area']??'')),'summary'=>trim((string)($_POST['summary']??'')),'latitude'=>(float)($_POST['latitude']??0),'longitude'=>(float)($_POST['longitude']??0),'images'=>$images,'image'=>$images[0]??'','published'=>isset($_POST['published'])];
        if ($record['title'] === '') throw new RuntimeException('タイトルは必須です。');
        $updated = false; foreach ($items as $key => $item) if (($item['id'] ?? '') === $id) { $items[$key] = $record; $updated = true; }
        if (!$updated) $items[] = $record; save_content('works', $items); redirect('works-admin.php?edit=' . rawurlencode($id) . '&saved=1');
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}
$currentImages = $edit ? work_images($edit) : [];
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>施工事例｜管理画面</title><link rel="stylesheet" href="assets/admin.css"><link rel="stylesheet" href="assets/admin-menu-fix.css"><link rel="stylesheet" href="assets/works-admin.css"></head><body class="admin-shell"><aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav></nav></aside><script src="assets/admin-nav.js?v=3" defer></script>
<main class="admin-main"><header><div><p>PRO CHUBO HIT OKINAWA</p><h1>施工事例</h1></div><a href="index.php#works" target="_blank">公開サイトを確認 ↗</a></header><?php if(isset($_GET['saved'])):?><p class="success">保存しました。</p><?php endif;?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
<div class="toolbar"><p><?=count($items)?>件登録</p><a class="primary" href="works-admin.php?edit=new">＋ 新規追加</a></div><div class="works-admin-layout"><section class="panel list"><table><thead><tr><th>施工事例</th><th>画像</th><th>状態</th><th></th></tr></thead><tbody><?php foreach($items as $item):$images=work_images($item);?><tr><td><strong><?=e($item['title']??'')?></strong><small><?=e(($item['region']??'').' / '.($item['area']??''))?></small></td><td><?=count($images)?> / 10</td><td><span class="status <?=!empty($item['published'])?'live':''?>"><?=!empty($item['published'])?'公開':'下書き'?></span></td><td><a href="works-admin.php?edit=<?=e($item['id'])?>">編集</a></td></tr><?php endforeach;?></tbody></table></section>
<?php if($editId):$v=$edit??[];?><section class="panel editor"><h2><?=$edit?'編集':'新規追加'?></h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e($edit['id']??'')?>"><label>タイトル<input name="title" required value="<?=e($v['title']??'')?>"></label><div class="fields"><label>カテゴリ<input name="category" value="<?=e($v['category']??'')?>"></label><label>エリア<select name="region"><option value="">選択してください</option><?php foreach(['本島北部','本島中部','本島南部','宮古','八重山','久米島・その他離島'] as $region):?><option <?=$region===($v['region']??'')?'selected':''?>><?=e($region)?></option><?php endforeach;?></select></label></div><label>市町村・表示地域<input name="area" value="<?=e($v['area']??'')?>"></label><label>概要<textarea name="summary" rows="5"><?=e($v['summary']??'')?></textarea></label><div class="fields"><label>緯度<input type="number" step="0.000001" name="latitude" value="<?=e($v['latitude']??26.2124)?>"></label><label>経度<input type="number" step="0.000001" name="longitude" value="<?=e($v['longitude']??127.6809)?>"></label></div>
<div class="gallery-admin"><div class="gallery-heading"><h3>施工写真</h3><span><?=count($currentImages)?> / 10枚</span></div><?php if($currentImages):?><div class="existing-images"><?php foreach($currentImages as $index=>$image):?><label><img src="<?=e($image)?>" alt="登録画像<?=($index+1)?>"><span><input type="checkbox" name="keep_images[]" value="<?=e($image)?>" checked> 使用する</span><small><?=$index===0?'メイン画像':'画像 '.($index+1)?></small></label><?php endforeach;?></div><?php endif;?><label class="upload-zone">画像を追加<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple><small>合計最大10枚／1枚6MBまで。先頭画像がメイン画像になります。</small></label></div><label class="check"><input type="checkbox" name="published" <?=!isset($v['published'])||$v['published']?'checked':''?>>公開する</label><button class="primary">保存する</button></form><?php if($edit):?><form method="post" onsubmit="return confirm('施工事例を削除しますか？')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=e($edit['id'])?>"><button class="danger">削除</button></form><?php endif;?></section><?php endif;?></div></main></body></html>
