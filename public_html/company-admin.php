<?php
require __DIR__ . '/lib.php';
if (!is_admin()) redirect('admin.php');
$profile = load_content('company')[0] ?? [];
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    try {
        $profile = [
            'id' => 'company-profile', 'logo' => upload_image('logo', (string)($profile['logo'] ?? '')),
            'company_name' => trim((string)($_POST['company_name'] ?? '')), 'company_name_en' => trim((string)($_POST['company_name_en'] ?? '')),
            'postal_code' => trim((string)($_POST['postal_code'] ?? '')), 'address' => trim((string)($_POST['address'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')), 'email' => trim((string)($_POST['email'] ?? '')),
            'hours' => trim((string)($_POST['hours'] ?? '')), 'closed_days' => trim((string)($_POST['closed_days'] ?? '')),
            'representative' => trim((string)($_POST['representative'] ?? '')), 'executive' => trim((string)($_POST['executive'] ?? '')),
            'affiliation' => trim((string)($_POST['affiliation'] ?? '')), 'description' => trim((string)($_POST['description'] ?? '')),
            'history' => trim((string)($_POST['history'] ?? '')),
        ];
        if ($profile['company_name'] === '') throw new RuntimeException('会社名は必須です。');
        save_content('company', [$profile]); redirect('company-admin.php?saved=1');
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ブランド・会社情報｜管理画面</title><link rel="stylesheet" href="assets/admin.css"><link rel="stylesheet" href="assets/company-admin.css"><link rel="stylesheet" href="assets/admin-menu-fix.css"></head><body class="admin-shell">
<aside><a class="admin-logo" href="admin.php">HIT OKINAWA<small>CONTENT MANAGEMENT</small></a><nav><a href="admin.php">ダッシュボード</a><a href="admin.php?tab=hero">HEROスライド</a><a href="admin.php?tab=works">施工事例</a><a href="admin.php?tab=news">最新情報</a><a class="active" href="company-admin.php">ブランド・会社情報</a></nav></aside><script src="assets/admin-nav.js?v=2" defer></script>
<main class="admin-main"><header><div><p>PRO KITCHEN HIT OKINAWA</p><h1>ブランド・会社情報</h1></div><a href="index.php" target="_blank">公開サイトを確認 ↗</a></header>
<?php if(isset($_GET['saved'])):?><p class="success">会社情報を保存しました。</p><?php endif;?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
<section class="panel editor company-editor"><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="logo-field"><div class="logo-preview"><?php if(!empty($profile['logo'])):?><img src="<?=e($profile['logo'])?>" alt="登録中のロゴ"><?php else:?><span>LOGO<br>PREVIEW</span><?php endif;?></div><label>ロゴ画像<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"><small>透過PNGまたはWebP推奨。6MBまで。</small></label></div>
<h2>基本情報</h2><div class="fields"><label>会社名<input name="company_name" required value="<?=e($profile['company_name']??'')?>"></label><label>英語表記<input name="company_name_en" value="<?=e($profile['company_name_en']??'')?>"></label></div><label>会社紹介<textarea name="description" rows="4"><?=e($profile['description']??'')?></textarea></label>
<div class="fields"><label>郵便番号<input name="postal_code" value="<?=e($profile['postal_code']??'')?>"></label><label>所在地<input name="address" value="<?=e($profile['address']??'')?>"></label><label>電話番号<input name="phone" value="<?=e($profile['phone']??'')?>"></label><label>メールアドレス<input type="email" name="email" value="<?=e($profile['email']??'')?>"></label><label>営業時間<input name="hours" value="<?=e($profile['hours']??'')?>"></label><label>定休日<input name="closed_days" value="<?=e($profile['closed_days']??'')?>"></label><label>代表<input name="representative" value="<?=e($profile['representative']??'')?>"></label><label>役員<input name="executive" value="<?=e($profile['executive']??'')?>"></label></div><label>所属<input name="affiliation" value="<?=e($profile['affiliation']??'')?>"></label>
<h2>沿革</h2><label>沿革項目<textarea name="history" rows="9" placeholder="2026年7月|プロ厨房HIT沖縄として事業開始"><?=e($profile['history']??'')?></textarea><small>「年月または見出し|説明」の形式で、1行につき1件入力します。</small></label><button class="primary">会社情報を保存する</button></form></section></main></body></html>
