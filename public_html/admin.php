<?php
require __DIR__ . '/lib.php';
$error = '';
$config = admin_config();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    try {
        if ($action === 'setup' && !$config) {
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            if (mb_strlen($username) < 3 || strlen($password) < 12) throw new RuntimeException('ユーザー名は3文字以上、パスワードは12文字以上にしてください。');
            save_admin(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'created_at' => date(DATE_ATOM)]);
            session_regenerate_id(true); $_SESSION['admin'] = $username; redirect('admin.php');
        }
        if ($action === 'login' && $config) {
            if (hash_equals((string)$config['username'], (string)($_POST['username'] ?? '')) && password_verify((string)($_POST['password'] ?? ''), (string)$config['password_hash'])) {
                session_regenerate_id(true); $_SESSION['admin'] = $config['username']; redirect('admin.php');
            }
            throw new RuntimeException('ログイン情報が正しくありません。');
        }
        if ($action === 'logout') { $_SESSION = []; session_destroy(); redirect('admin.php'); }
        if (!is_admin()) throw new RuntimeException('ログインしてください。');
        if ($action === 'delete') {
            $type = in_array($_POST['type'] ?? '', ['hero','works','news'], true) ? $_POST['type'] : '';
            $items = array_values(array_filter(load_content($type), fn($x) => ($x['id'] ?? '') !== ($_POST['id'] ?? '')));
            save_content($type, $items); redirect('admin.php?tab=' . $type . '&saved=1');
        }
        if ($action === 'save') {
            $type = in_array($_POST['type'] ?? '', ['hero','works','news'], true) ? $_POST['type'] : '';
            if (!$type) throw new RuntimeException('保存対象が不正です。');
            $items = load_content($type); $id = trim((string)($_POST['id'] ?? '')) ?: $type . '-' . bin2hex(random_bytes(5));
            $existing = null; foreach ($items as $item) if (($item['id'] ?? '') === $id) $existing = $item;
            if ($type === 'hero') $record = ['id'=>$id,'title'=>trim((string)$_POST['title']),'lead'=>trim((string)$_POST['lead']),'color'=>(string)$_POST['color'],'image'=>upload_image('image',(string)($existing['image']??'')),'overlay'=>(string)$_POST['overlay'],'overlay_opacity'=>max(0,min(100,(int)$_POST['overlay_opacity'])),'dots'=>isset($_POST['dots']),'dots_opacity'=>max(0,min(100,(int)$_POST['dots_opacity'])),'published'=>isset($_POST['published'])];
            elseif ($type === 'works') $record = ['id'=>$id,'title'=>trim((string)$_POST['title']),'category'=>trim((string)$_POST['category']),'area'=>trim((string)$_POST['area']),'summary'=>trim((string)$_POST['summary']),'latitude'=>(float)$_POST['latitude'],'longitude'=>(float)$_POST['longitude'],'image'=>upload_image('image',(string)($existing['image']??'')),'published'=>isset($_POST['published'])];
            elseif ($type === 'news') $record = ['id'=>$id,'title'=>trim((string)$_POST['title']),'category'=>trim((string)$_POST['category']),'body'=>trim((string)$_POST['body']),'published_at'=>(string)$_POST['published_at'],'published'=>isset($_POST['published'])];
            else $record = ['id'=>'company-profile','title'=>trim((string)$_POST['company_name']),'logo'=>upload_image('logo',(string)($existing['logo']??'')),'company_name'=>trim((string)$_POST['company_name']),'company_name_en'=>trim((string)$_POST['company_name_en']),'postal_code'=>trim((string)$_POST['postal_code']),'address'=>trim((string)$_POST['address']),'phone'=>trim((string)$_POST['phone']),'email'=>trim((string)$_POST['email']),'hours'=>trim((string)$_POST['hours']),'closed_days'=>trim((string)$_POST['closed_days']),'representative'=>trim((string)$_POST['representative']),'executive'=>trim((string)$_POST['executive']),'affiliation'=>trim((string)$_POST['affiliation']),'description'=>trim((string)$_POST['description']),'history'=>trim((string)$_POST['history'])];
            if (!$record['title']) throw new RuntimeException('タイトルは必須です。');
            $updated=false; foreach($items as $key=>$item) if(($item['id']??'')===$id){$items[$key]=$record;$updated=true;break;} if(!$updated)$items[]=$record;
            save_content($type,$items); redirect('admin.php?tab='.$type.'&saved=1');
        }
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}

if (!is_admin()): ?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>管理画面</title><link rel="stylesheet" href="assets/admin.css"></head><body class="auth"><main class="auth-card"><p class="admin-mark">PRO KITCHEN HIT / CMS</p><h1><?= $config ? '管理画面ログイン' : '管理者の初期登録' ?></h1><?php if($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="<?= $config ? 'login' : 'setup' ?>"><label>ユーザー名<input name="username" required autocomplete="username"></label><label>パスワード<input type="password" name="password" required minlength="12" autocomplete="<?= $config ? 'current-password' : 'new-password' ?>"></label><button> <?= $config ? 'ログイン' : '管理者を作成' ?> </button></form><a href="index.php">← サイトへ戻る</a></main></body></html><?php exit; endif;

$requestedTab = (string)($_GET['tab'] ?? 'dashboard');
if ($requestedTab === 'works') redirect('works-admin.php');
if ($requestedTab === 'news') redirect('news-admin.php');
$tab = in_array($requestedTab, ['dashboard', 'hero', 'works', 'news'], true) ? $requestedTab : 'dashboard';
$editId=(string)($_GET['edit']??''); $items=$tab==='dashboard'?[]:load_content($tab); $edit=null; foreach($items as $item)if(($item['id']??'')===$editId)$edit=$item;
?><!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>管理画面｜<?= APP_NAME ?></title><link rel="stylesheet" href="assets/admin.css"></head><body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav><a class="<?= $tab==='dashboard'?'active':'' ?>" href="admin.php">ダッシュボード</a><a class="<?= $tab==='hero'?'active':'' ?>" href="?tab=hero">HEROスライド</a><a class="<?= $tab==='works'?'active':'' ?>" href="?tab=works">施工事例</a><a class="<?= $tab==='news'?'active':'' ?>" href="?tab=news">最新情報</a><a href="inquiries-admin.php">お問い合わせ</a></nav><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="logout"><button class="text-button">ログアウト</button></form></aside>
<main class="admin-main"><header><div><p>PRO KITCHEN HIT OKINAWA</p><h1><?= ['dashboard'=>'ダッシュボード','hero'=>'HEROスライド','works'=>'施工事例','news'=>'最新情報'][$tab] ?></h1></div><a href="index.php" target="_blank">公開サイトを確認 ↗</a></header><?php if(isset($_GET['saved'])):?><p class="success">保存しました。</p><?php endif;?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
<?php if($tab==='dashboard'): $counts=['hero'=>load_content('hero'),'works'=>load_content('works'),'news'=>load_content('news')]; ?><div class="stats"><?php foreach(['hero'=>'HERO','works'=>'施工事例','news'=>'最新情報'] as $key=>$label):?><a href="<?=$key==='works'?'works-admin.php':'?tab='.$key?>"><span><?=count($counts[$key])?></span><?=$label?><small>公開 <?=count(published($counts[$key]))?>件</small></a><?php endforeach;?></div><section class="panel"><h2>運用の中心</h2><ol><li>HERO画像と見え方を調整</li><li>地図と施工事例を登録</li><li>最新情報を公開・更新</li></ol><p>画像はJPEG・PNG・WebP、6MBまで。公開前に会社情報と問い合わせ先を確定してください。</p></section>
<?php else: ?><div class="toolbar"><p><?=count($items)?>件登録</p><a class="primary" href="?tab=<?=e($tab)?>&edit=new">＋ 新規追加</a></div><div class="content-grid"><section class="panel list"><table><thead><tr><th>タイトル</th><th>状態</th><th></th></tr></thead><tbody><?php foreach($items as $item):?><tr><td><strong><?=e($item['title'])?></strong><small><?=e($item['area']??$item['category']??'')?></small></td><td><span class="status <?=!empty($item['published'])?'live':''?>"><?=!empty($item['published'])?'公開':'下書き'?></span></td><td><a href="?tab=<?=e($tab)?>&edit=<?=e($item['id'])?>">編集</a></td></tr><?php endforeach;?></tbody></table></section>
<?php if($editId): $v=$edit??[]; ?><section class="panel editor"><h2><?=$edit?'編集':'新規追加'?></h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="type" value="<?=e($tab)?>"><input type="hidden" name="id" value="<?=e($edit['id']??'')?>"><label>タイトル<input name="title" required value="<?=e($v['title']??'')?>"></label>
<?php if($tab==='hero'):?><label>リード<textarea name="lead"><?=e($v['lead']??'')?></textarea></label><div class="fields"><label>背景色<input type="color" name="color" value="<?=e($v['color']??'#777b7d')?>"></label><label>オーバーレイ色<input type="color" name="overlay" value="<?=e($v['overlay']??'#102a43')?>"></label></div><label>オーバーレイ濃度 <output data-overlay-out><?=e($v['overlay_opacity']??35)?>%</output><input data-overlay type="range" name="overlay_opacity" min="0" max="100" value="<?=e($v['overlay_opacity']??35)?>"></label><label class="check"><input type="checkbox" name="dots" <?=!isset($v['dots'])||$v['dots']?'checked':''?>>ドット柄を表示</label><label>ドット濃度 <output data-dots-out><?=e($v['dots_opacity']??18)?>%</output><input data-dots type="range" name="dots_opacity" min="0" max="100" value="<?=e($v['dots_opacity']??18)?>"></label><div class="preview" data-preview style="--preview-bg:<?=e($v['color']??'#777b7d')?>;--preview-overlay:<?=e($v['overlay']??'#102a43')?>;--preview-opacity:<?=e((($v['overlay_opacity']??35)/100))?>"><strong><?=e($v['title']??'HEROプレビュー')?></strong></div>
<?php elseif($tab==='works'):?><div class="fields"><label>カテゴリ<input name="category" value="<?=e($v['category']??'')?>"></label><label>エリア<input name="area" value="<?=e($v['area']??'')?>"></label></div><label>概要<textarea name="summary"><?=e($v['summary']??'')?></textarea></label><div class="fields"><label>緯度<input type="number" step="0.000001" name="latitude" value="<?=e($v['latitude']??26.2124)?>"></label><label>経度<input type="number" step="0.000001" name="longitude" value="<?=e($v['longitude']??127.6809)?>"></label></div><p class="hint">座標は地図サービスで取得した緯度・経度を入力します。住所非公開案件は概略座標を使用してください。</p>
<?php else:?><label>カテゴリ<input name="category" value="<?=e($v['category']??'お知らせ')?>"></label><label>本文<textarea name="body" rows="8"><?=e($v['body']??'')?></textarea></label><label>公開日<input type="date" name="published_at" value="<?=e($v['published_at']??date('Y-m-d'))?>"></label><?php endif;?>
<?php if($tab!=='news'):?><label>画像（JPEG・PNG・WebP／6MBまで）<input type="file" name="image" accept="image/jpeg,image/png,image/webp"><small>※長辺1920pxを超える画像は、比率を保って自動縮小します。</small></label><?php endif;?><label class="check"><input type="checkbox" name="published" <?=!isset($v['published'])||$v['published']?'checked':''?>>公開する</label><button class="primary">保存する</button></form><?php if($edit):?><form method="post" onsubmit="return confirm('削除しますか？')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="<?=e($tab)?>"><input type="hidden" name="id" value="<?=e($edit['id'])?>"><button class="danger">削除</button></form><?php endif;?></section><?php endif;?></div><?php endif;?></main><script src="assets/admin-nav.js?v=5" defer></script><script src="assets/admin.js"></script></body></html>
