<?php
require __DIR__ . '/lib.php';
$company = load_content('company')[0] ?? [];
$types = inquiry_types();
$errors = [];
$values = $_SESSION['contact_values'] ?? ['type'=>'','name'=>'','company'=>'','phone'=>'','email'=>'','message'=>''];
$step = 'input';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? 'confirm');
    if ($action === 'back') {
        $step = 'input';
    } else {
        $values = [
            'type' => trim((string)($_POST['type'] ?? '')),
            'name' => trim((string)($_POST['name'] ?? '')),
            'company' => trim((string)($_POST['company'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'message' => trim((string)($_POST['message'] ?? '')),
        ];
        if (!in_array($values['type'], $types, true)) $errors['type'] = '相談種別を選択してください。';
        if ($values['name'] === '' || mb_strlen($values['name']) > 80) $errors['name'] = 'お名前を入力してください。';
        if ($values['phone'] === '' || !preg_match('/^[0-9+()\-\s]{8,25}$/', $values['phone'])) $errors['phone'] = '電話番号を正しく入力してください。';
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || strlen($values['email']) > 254) $errors['email'] = 'メールアドレスを正しく入力してください。';
        if ($values['message'] === '' || mb_strlen($values['message']) > 3000) $errors['message'] = 'お問い合わせ内容を3000文字以内で入力してください。';
        if (empty($_POST['privacy'])) $errors['privacy'] = '個人情報の取り扱いへの同意が必要です。';
        if ((string)($_POST['website'] ?? '') !== '') $errors['form'] = '送信できませんでした。';
        $_SESSION['contact_values'] = $values;
        if (!$errors && $action === 'confirm') {
            $_SESSION['contact_confirmed'] = true;
            $step = 'confirm';
        } elseif (!$errors && $action === 'send' && !empty($_SESSION['contact_confirmed'])) {
            $lastSent = (int)($_SESSION['contact_last_sent'] ?? 0);
            if ($lastSent > time() - 60) {
                $errors['form'] = '連続して送信できません。少し時間をおいてください。';
                $step = 'confirm';
            } else {
                $inquiry = $values + [
                    'id' => 'INQ-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2))),
                    'status' => 'new',
                    'created_at' => date(DATE_ATOM),
                    'mail_sent' => false,
                ];
                save_inquiry($inquiry);
                $inquiry['mail_sent'] = notify_inquiry($inquiry, (string)($company['email'] ?? ''));
                $items = load_content('inquiries');
                foreach ($items as &$item) if (($item['id'] ?? '') === $inquiry['id']) $item['mail_sent'] = $inquiry['mail_sent'];
                unset($item); save_content('inquiries', $items);
                $_SESSION['contact_last_sent'] = time();
                $_SESSION['contact_sent_id'] = $inquiry['id'];
                unset($_SESSION['contact_values'], $_SESSION['contact_confirmed']);
                redirect('contact.php?thanks=1');
            }
        }
    }
}
if (isset($_GET['thanks']) && !empty($_SESSION['contact_sent_id'])) $step = 'thanks';
function field_error(array $errors, string $field): string { return isset($errors[$field]) ? '<small class="field-error">'.e($errors[$field]).'</small>' : ''; }
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>お問い合わせ｜<?=e(APP_NAME)?></title><meta name="description" content="飲食店の開業、厨房設計、厨房機器、店舗工事、店舗売却についてお気軽にご相談ください。"><link rel="stylesheet" href="assets/contact.css"></head><body>
<header class="contact-header"><a href="index.php"><?=!empty($company['logo'])?'<img src="'.e($company['logo']).'" alt="'.e($company['company_name']??APP_NAME).'">':'<strong>'.e(APP_NAME).'</strong>'?></a><a href="index.php">サイトへ戻る</a></header>
<main class="contact-main"><div class="contact-title"><p>CONTACT</p><h1>お問い合わせ</h1><span>開業前の構想段階から、お気軽にご相談ください。</span></div>
<?php if($step==='thanks'): ?>
<section class="contact-card thanks"><span>THANK YOU</span><h2>お問い合わせを受け付けました。</h2><p>内容を確認のうえ、担当者よりご連絡いたします。<br>受付番号：<?=e($_SESSION['contact_sent_id'])?></p><a class="contact-button" href="index.php">トップページへ戻る</a></section>
<?php elseif($step==='confirm'): ?>
<section class="contact-card"><div class="steps"><span>01 入力</span><strong>02 確認</strong><span>03 完了</span></div><h2>入力内容をご確認ください</h2><?php if(isset($errors['form'])):?><p class="form-error"><?=e($errors['form'])?></p><?php endif;?>
<dl class="confirm-list"><div><dt>相談種別</dt><dd><?=e($values['type'])?></dd></div><div><dt>お名前</dt><dd><?=e($values['name'])?></dd></div><div><dt>会社・店舗名</dt><dd><?=e($values['company']?:'—')?></dd></div><div><dt>電話番号</dt><dd><?=e($values['phone'])?></dd></div><div><dt>メールアドレス</dt><dd><?=e($values['email'])?></dd></div><div><dt>お問い合わせ内容</dt><dd><?=nl2br(e($values['message']))?></dd></div></dl>
<div class="confirm-actions"><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="back"><button class="back-button">入力内容を修正</button></form><form method="post"><?php foreach($values as $key=>$value):?><input type="hidden" name="<?=e($key)?>" value="<?=e($value)?>"><?php endforeach;?><input type="hidden" name="website" value=""><input type="hidden" name="privacy" value="1"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="send"><button class="contact-button">この内容で送信</button></form></div></section>
<?php else: ?>
<section class="contact-card"><div class="steps"><strong>01 入力</strong><span>02 確認</span><span>03 完了</span></div><p class="required-note"><b>必須</b>の項目は必ずご入力ください。</p><?php if(isset($errors['form'])):?><p class="form-error"><?=e($errors['form'])?></p><?php endif;?>
<form method="post" class="contact-form" novalidate><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="confirm"><label class="honey">ウェブサイト<input name="website" tabindex="-1" autocomplete="off"></label>
<label><span>相談種別 <b>必須</b></span><select name="type" required><option value="">選択してください</option><?php foreach($types as $type):?><option <?=($values['type']??'')===$type?'selected':''?>><?=e($type)?></option><?php endforeach;?></select><?=field_error($errors,'type')?></label>
<div class="form-row"><label><span>お名前 <b>必須</b></span><input name="name" required autocomplete="name" value="<?=e($values['name']??'')?>"><?=field_error($errors,'name')?></label><label><span>会社・店舗名</span><input name="company" autocomplete="organization" value="<?=e($values['company']??'')?>"></label></div>
<div class="form-row"><label><span>電話番号 <b>必須</b></span><input name="phone" required inputmode="tel" autocomplete="tel" value="<?=e($values['phone']??'')?>"><?=field_error($errors,'phone')?></label><label><span>メールアドレス <b>必須</b></span><input type="email" name="email" required autocomplete="email" value="<?=e($values['email']??'')?>"><?=field_error($errors,'email')?></label></div>
<label><span>お問い合わせ内容 <b>必須</b></span><textarea name="message" rows="9" maxlength="3000" required placeholder="ご相談内容や開業予定時期、ご希望のエリアなどをご記入ください。"><?=e($values['message']??'')?></textarea><?=field_error($errors,'message')?></label>
<label class="privacy-check"><input type="checkbox" name="privacy" value="1" required><span><a href="privacy.php" target="_blank">個人情報の取り扱い</a>に同意する</span></label><?=field_error($errors,'privacy')?>
<button class="contact-button">入力内容を確認する</button></form></section><?php endif;?></main>
<footer class="contact-footer">© <?=date('Y')?> <?=e($company['company_name_en']??'PRO CHUBO HIT OKINAWA')?></footer></body></html>
