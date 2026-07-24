<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');

$items = load_content('news');
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
        $id = trim((string)($_POST['id'] ?? ''));
        if ($action === 'delete') {
            $items = array_values(array_filter($items, fn(array $item): bool => ($item['id'] ?? '') !== $id));
            save_content('news', $items);
            redirect('news-admin.php?saved=1');
        }

        if ($id === '') $id = 'news-' . bin2hex(random_bytes(5));
        $existing = null;
        foreach ($items as $item) {
            if (($item['id'] ?? '') === $id) $existing = $item;
        }

        $existingImages = [];
        foreach ((array)($existing['blocks'] ?? []) as $blockIndex => $existingBlock) {
            if (($existingBlock['type'] ?? '') !== 'image') continue;
            $existingKey = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($existingBlock['id'] ?? 'block-' . $blockIndex));
            $existingImages[$existingKey] = (string)($existingBlock['image'] ?? '');
        }
        $blocks = [];
        foreach (array_slice((array)($_POST['block_key'] ?? []), 0, 40) as $rawKey) {
            $key = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$rawKey);
            if ($key === '') continue;
            $type = (string)($_POST['block_type'][$key] ?? '');
            if ($type === 'text') {
                $text = trim((string)($_POST['block_text'][$key] ?? ''));
                if ($text !== '') $blocks[] = ['id'=>$key, 'type'=>'text', 'text'=>$text];
            } elseif ($type === 'image') {
                $current = (string)($existingImages[$key] ?? '');
                $image = upload_image('block_image_' . $key, $current);
                if ($image !== '') $blocks[] = ['id'=>$key, 'type'=>'image', 'image'=>$image];
            }
        }

        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') throw new RuntimeException('タイトルは必須です。');
        $textBlocks = array_values(array_filter($blocks, fn(array $block): bool => $block['type'] === 'text'));
        $record = [
            'id' => $id,
            'title' => $title,
            'category' => trim((string)($_POST['category'] ?? 'お知らせ')),
            'body' => implode("\n\n", array_column($textBlocks, 'text')),
            'blocks' => $blocks,
            'published_at' => (string)($_POST['published_at'] ?? date('Y-m-d')),
            'published' => isset($_POST['published']),
        ];

        $updated = false;
        foreach ($items as $index => $item) {
            if (($item['id'] ?? '') === $id) {
                $items[$index] = $record;
                $updated = true;
                break;
            }
        }
        if (!$updated) $items[] = $record;
        save_content('news', $items);
        redirect('news-admin.php?edit=' . rawurlencode($id) . '&saved=1');
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

usort($items, static fn(array $a, array $b): int => strcmp((string)($b['published_at']??''), (string)($a['published_at']??'')));
$blocks = $edit['blocks'] ?? [];
if ($blocks === [] && !empty($edit['body'])) {
    $blocks = [['id'=>'legacy-text', 'type'=>'text', 'text'=>(string)$edit['body']]];
}
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>最新情報管理</title><link rel="stylesheet" href="assets/admin.css"><link rel="stylesheet" href="assets/admin-menu-fix.css"><link rel="stylesheet" href="assets/news-admin.css"></head>
<body class="admin-shell"><aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav></nav></aside><script src="assets/admin-nav.js?v=6" defer></script>
<main class="admin-main"><header><div><p>PRO CHUBO HIT OKINAWA</p><h1>最新情報</h1></div><a href="news.php" target="_blank">公開ページを確認 ↗</a></header>
<?php if(isset($_GET['saved'])):?><p class="success">保存しました。</p><?php endif;?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
<div class="toolbar"><p><?=count($items)?>件登録</p><a class="primary" href="news-admin.php?edit=new">＋ 新規追加</a></div>
<div class="news-admin-layout"><section class="panel list"><table><thead><tr><th>公開日</th><th>タイトル</th><th>状態</th><th></th></tr></thead><tbody>
<?php foreach($items as $item):?><tr><td><?=e($item['published_at']??'')?></td><td><strong><?=e($item['title']??'')?></strong><small><?=e($item['category']??'')?></small></td><td><span class="status <?=!empty($item['published'])?'live':''?>"><?=!empty($item['published'])?'公開':'非公開'?></span></td><td><a href="news-admin.php?edit=<?=e($item['id']??'')?>">編集</a></td></tr><?php endforeach;?>
</tbody></table></section>
<?php if($editId): $v=$edit??[]; ?>
<section class="panel editor"><h2><?=$edit?'編集':'新規追加'?></h2>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e($edit['id']??'')?>">
<label>タイトル<input name="title" required value="<?=e($v['title']??'')?>"></label><div class="fields"><label>カテゴリ<input name="category" value="<?=e($v['category']??'お知らせ')?>"></label><label>公開日<input type="date" name="published_at" value="<?=e($v['published_at']??date('Y-m-d'))?>"></label></div>
<div class="news-block-editor" data-news-block-editor><div class="news-block-toolbar"><strong>記事内容</strong><div><button type="button" data-add-text>＋ テキスト</button><button type="button" data-add-image>＋ 写真</button></div></div><p class="hint">テキストと写真を、記事に表示したい順番で追加してください。</p><div data-news-block-list>
<?php foreach($blocks as $index=>$block): $key=preg_replace('/[^a-zA-Z0-9_-]/','',(string)($block['id']??'block-'.$index)); ?>
<section class="news-edit-block" data-news-block><input type="hidden" name="block_key[]" value="<?=e($key)?>"><input type="hidden" name="block_type[<?=e($key)?>]" value="<?=e($block['type']??'text')?>"><header><strong><?=($block['type']??'text')==='image'?'写真':'テキスト'?></strong><div><button type="button" data-move-up>↑</button><button type="button" data-move-down>↓</button><button type="button" data-remove-block>削除</button></div></header>
<?php if(($block['type']??'text')==='image'):?><input type="hidden" name="block_existing_image[<?=e($key)?>]" value="<?=e($block['image']??'')?>"><?php if(!empty($block['image'])):?><img src="<?=e($block['image'])?>" alt="登録中の写真"><?php endif;?><label>写真<input type="file" name="block_image_<?=e($key)?>" accept="image/jpeg,image/png,image/webp"><small>※長辺1920pxを超える画像は、比率を保って自動縮小します。</small></label>
<?php else:?><label>本文<textarea name="block_text[<?=e($key)?>]" rows="7"><?=e($block['text']??'')?></textarea></label><?php endif;?></section>
<?php endforeach;?></div></div>
<label class="check"><input type="checkbox" name="published" <?=!isset($v['published'])||$v['published']?'checked':''?>>公開する</label><button class="primary">保存する</button></form>
<?php if($edit):?><form method="post" onsubmit="return confirm('この記事を削除しますか？')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=e($edit['id'])?>"><button class="danger">削除</button></form><?php endif;?>
</section><?php endif;?></div></main><script src="assets/news-admin.js?v=1" defer></script></body></html>
