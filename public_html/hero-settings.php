<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');
$settings = load_content('hero_settings')[0] ?? [];
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $settings = [
            'id' => 'hero-settings',
            'overlay' => preg_match('/^#[0-9a-f]{6}$/i', (string)($_POST['overlay'] ?? '')) ? (string)$_POST['overlay'] : '#102a43',
            'overlay_opacity' => max(0, min(100, (int)($_POST['overlay_opacity'] ?? 35))),
            'dots' => isset($_POST['dots']),
            'dots_opacity' => max(0, min(100, (int)($_POST['dots_opacity'] ?? 18))),
        ];
        save_content('hero_settings', [$settings]);
        redirect('hero-settings.php?saved=1');
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HERO共通設定｜管理画面</title><link rel="stylesheet" href="assets/admin.css"><link rel="stylesheet" href="assets/admin-menu-fix.css"><link rel="stylesheet" href="assets/admin-hero-fields.css"><link rel="stylesheet" href="assets/hero-settings-admin.css"></head><body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav><a href="admin.php">ダッシュボード</a><a href="admin.php?tab=hero">HEROスライド</a><a class="active" href="hero-settings.php">HERO共通設定</a><a href="admin.php?tab=works">施工事例</a><a href="admin.php?tab=news">最新情報</a><a href="company-admin.php">ブランド・会社情報</a></nav></aside><script src="assets/admin-nav.js?v=5" defer></script>
<main class="admin-main"><header><div><p>PRO CHUBO HIT OKINAWA</p><h1>HERO共通設定</h1></div><a href="index.php" target="_blank">公開サイトを確認 ↗</a></header><?php if(isset($_GET['saved'])):?><p class="success">共通設定を保存しました。</p><?php endif;?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
<div class="settings-layout"><section class="panel editor"><p class="setting-intro">この設定は、公開中のすべてのHEROスライドへ共通で適用されます。</p><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>オーバーレイ色<input id="overlay-color" type="color" name="overlay" value="<?=e($settings['overlay']??'#102a43')?>"><output class="color-value"><?=e(strtoupper($settings['overlay']??'#102a43'))?></output></label><label>オーバーレイ濃度 <output id="overlay-output"><?=e($settings['overlay_opacity']??35)?>%</output><input id="overlay-opacity" type="range" name="overlay_opacity" min="0" max="100" value="<?=e($settings['overlay_opacity']??35)?>"></label><label class="check"><input id="dots-enabled" type="checkbox" name="dots" <?=!empty($settings['dots'])?'checked':''?>>ドット柄を表示する</label><label>ドット濃度 <output id="dots-output"><?=e($settings['dots_opacity']??18)?>%</output><input id="dots-opacity" type="range" name="dots_opacity" min="0" max="100" value="<?=e($settings['dots_opacity']??18)?>"></label><button class="primary">共通設定を保存する</button></form></section><aside class="effect-preview" id="effect-preview"><div class="preview-dots"></div><div class="preview-copy"><small>PRO CHUBO HIT OKINAWA</small><strong>HERO EFFECT PREVIEW</strong><p>画像上でのオーバーレイとドット柄を確認できます。</p></div></aside></div></main><script src="assets/hero-settings-admin.js"></script></body></html>
