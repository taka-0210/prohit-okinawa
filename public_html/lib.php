<?php
declare(strict_types=1);

const APP_NAME = 'プロ厨房HIT沖縄';
// ローカルではプロジェクト直下、サブドメイン公開時は public_html の外へ保存する。
define('DATA_DIR', basename(dirname(__DIR__)) === 'public_html'
    ? dirname(__DIR__, 2) . '/storage-demo'
    : __DIR__ . '/../storage');
const UPLOAD_DIR = __DIR__ . '/uploads';

function boot_app(): void
{
    $sessionDirectory = DATA_DIR . '/sessions';
    if (!is_dir($sessionDirectory)) {
        mkdir($sessionDirectory, 0775, true);
    }
    session_save_path($sessionDirectory);
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => isset($_SERVER['HTTPS'])]);
        session_start();
    }
    foreach ([DATA_DIR, DATA_DIR . '/content', UPLOAD_DIR] as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
    seed_content();
}

function seed_content(): void
{
    $defaults = [
        'hero' => [
            ['id' => 'hero-1', 'title' => '沖縄を、飲食店から元気にする。', 'lead' => '構想から開業、その先まで。飲食店づくりを一社で支えます。', 'color' => '#777b7d', 'image' => '', 'overlay' => '#102a43', 'overlay_opacity' => 38, 'dots' => true, 'dots_opacity' => 18, 'published' => true],
            ['id' => 'hero-2', 'title' => '機械ではなく、繁盛するお店を考える。', 'lead' => '経営と現場、両方の経験から厨房と店舗をご提案します。', 'color' => '#e9e2d3', 'image' => '', 'overlay' => '#1d3557', 'overlay_opacity' => 24, 'dots' => true, 'dots_opacity' => 14, 'published' => true],
            ['id' => 'hero-3', 'title' => '開業から、設備更新、店舗売却まで。', 'lead' => '飲食店のライフサイクルに、長く寄り添うパートナーです。', 'color' => '#14283d', 'image' => '', 'overlay' => '#07182a', 'overlay_opacity' => 48, 'dots' => true, 'dots_opacity' => 18, 'published' => true],
        ],
        'works' => [
            ['id' => 'work-1', 'title' => '那覇市・飲食店 厨房導入', 'category' => '新規開業', 'area' => '那覇市', 'summary' => '動線と将来の設備更新まで見据えた厨房計画。', 'latitude' => 26.2124, 'longitude' => 127.6809, 'image' => '', 'published' => true],
            ['id' => 'work-2', 'title' => '沖縄市・店舗改装', 'category' => '内外装工事', 'area' => '沖縄市', 'summary' => '既存設備を活かしながら店舗全体をリニューアル。', 'latitude' => 26.3344, 'longitude' => 127.8056, 'image' => '', 'published' => true],
        ],
        'news' => [
            ['id' => 'news-1', 'title' => 'プロ厨房HIT沖縄 Webサイト準備中', 'category' => 'お知らせ', 'body' => '現在、Webサイトの公開準備を進めています。', 'published_at' => date('Y-m-d'), 'published' => true],
        ],
        'company' => [[
            'id' => 'company-profile', 'logo' => '',
            'company_name' => '株式会社 プロ厨房HIT 沖縄', 'company_name_en' => 'PRO CHUBO HIT OKINAWA',
            'postal_code' => '', 'address' => '沖縄県', 'phone' => '', 'email' => '',
            'hours' => '', 'closed_days' => '', 'representative' => '新垣 大作', 'executive' => '高見 昌也',
            'affiliation' => 'プロ厨房HIT フランチャイズ加盟店',
            'description' => '飲食店の開業準備から店舗づくり、設備導入、工事、開業後、店舗売却までトータルでサポートします。',
            'history' => "プロ厨房HIT沖縄 設立|飲食店づくりのトータルサポートを開始\nウリテン沖縄 展開|店舗の居抜き売買支援を開始",
        ]],
        'services' => [[
            'id' => 'kitchen-design-opening',
            'title' => '厨房設計・開業支援',
            'title_en' => 'KITCHEN DESIGN & OPENING SUPPORT',
            'lead' => '現地調査から厨房設計、機器選定、搬入設置まで。お店づくりを一つの窓口で支えます。',
            'intro' => '飲食店の厨房は、機器を並べるだけでは完成しません。スタッフの動き、メニュー、提供数、将来の設備更新まで見据え、経営と現場の両方に合う厨房をご提案します。',
            'sections' => [
                ['heading'=>'現地を知ることから始めます','body'=>'店舗の広さ、設備条件、給排水、電気、ガス、排気などを確認し、計画の土台を整えます。','image'=>''],
                ['heading'=>'動きやすい厨房レイアウト','body'=>'調理工程とスタッフの動線を整理し、無駄な移動を減らす厨房レイアウトを設計します。','image'=>''],
                ['heading'=>'CAD図面で具体化','body'=>'打ち合わせ内容を図面に落とし込み、機器寸法や作業スペースを事前に確認できる状態にします。','image'=>''],
                ['heading'=>'予算に合う機器選定','body'=>'新品と中古を組み合わせ、初期投資と長期運用のバランスを考えた設備をご提案します。','image'=>''],
                ['heading'=>'搬入・設置、開業まで伴走','body'=>'機器の搬入設置や工事連携まで一括して進行し、安心して開業日を迎えられるよう支えます。','image'=>''],
            ],
            'published' => true,
        ]],
    ];
    foreach ($defaults as $name => $value) {
        $path = content_path($name);
        if (!is_file($path)) save_content($name, $value);
    }
    $company = load_content('company');
    if (($company[0]['company_name_en'] ?? '') === 'PRO KITCHEN HIT OKINAWA') {
        $company[0]['company_name_en'] = 'PRO CHUBO HIT OKINAWA';
        save_content('company', $company);
    }
    if (($company[0]['company_name'] ?? '') === 'プロ厨房HIT沖縄') {
        $company[0]['company_name'] = '株式会社 プロ厨房HIT 沖縄';
        save_content('company', $company);
    }
    if (!is_file(content_path('hero_settings'))) {
        $firstHero = load_content('hero')[0] ?? [];
        save_content('hero_settings', [[
            'id' => 'hero-settings',
            'overlay' => $firstHero['overlay'] ?? '#102a43',
            'overlay_opacity' => (int)($firstHero['overlay_opacity'] ?? 35),
            'dots' => !empty($firstHero['dots']),
            'dots_opacity' => (int)($firstHero['dots_opacity'] ?? 18),
        ]]);
    }
    $serviceSeeds = [
        ['id'=>'kitchen-design-opening','title'=>'厨房設計・開業支援','title_en'=>'KITCHEN DESIGN & OPENING SUPPORT','lead'=>'現地調査から厨房設計、機器選定、搬入設置まで。お店づくりを一つの窓口で支えます。','intro'=>'飲食店の厨房は、機器を並べるだけでは完成しません。スタッフの動き、メニュー、提供数、将来の設備更新まで見据えてご提案します。','headings'=>['現地を知ることから始めます','動きやすい厨房レイアウト','CAD図面で具体化','予算に合う機器選定','搬入・設置、開業まで伴走']],
        ['id'=>'equipment-sales-purchase','title'=>'厨房機器 販売・買取','title_en'=>'EQUIPMENT SALES & PURCHASE','lead'=>'新品・中古の販売から、設備更新や閉店時の買取まで柔軟に対応します。','intro'=>'全国のプロ厨房HITネットワークを活かし、予算と用途に合う厨房機器を選定。導入後や入替えまで長く支えます。','headings'=>['必要な機器を整理','新品と中古を最適に選定','全国ネットワークで調達','搬入・設置まで対応','入替え・買取も相談']],
        ['id'=>'interior-exterior','title'=>'内装・外装工事','title_en'=>'INTERIOR & EXTERIOR CONSTRUCTION','lead'=>'厨房だけでなく、客席や外観まで。お店全体を一つのコンセプトで形にします。','intro'=>'厨房計画と店舗デザイン、設備工事を分断せず、営業しやすくお客様に選ばれる空間をつくります。','headings'=>['現地調査とご要望確認','店舗コンセプトを整理','厨房と客席を一体設計','内装・外装工事を進行','完成・引き渡し']],
        ['id'=>'uriten','title'=>'ウリテン事業','title_en'=>'URITEN BUSINESS','lead'=>'居抜き物件を探す方、店舗を譲りたい方。次の営業へ想いをつなぎます。','intro'=>'店舗紹介、売却相談、設備や工事の調整まで、飲食店経営者の視点で居抜き売買をサポートします。','headings'=>['ご希望・ご事情を確認','居抜き物件をご紹介','現地と設備を確認','契約・引き継ぎを支援','開業・売却後もサポート']],
        ['id'=>'okinawa-opening','title'=>'沖縄での飲食店開業サポート','title_en'=>'OPEN A RESTAURANT IN OKINAWA','lead'=>'沖縄でお店を始めたい。その構想を、物件探しから開業まで現地で支えます。','intro'=>'県内の方はもちろん、県外からの出店にも対応。沖縄の地域事情や物流、設備条件を踏まえて伴走します。','headings'=>['沖縄で実現したい構想を確認','出店エリア・物件探し','現地条件と予算を整理','店舗・厨房づくり','開業準備を現地で支援']],
        ['id'=>'rational','title'=>'ラショナル製品の導入支援','title_en'=>'RATIONAL INTRODUCTION SUPPORT','lead'=>'現役ユーザーだから伝えられる、実際の使い勝手と導入効果があります。','intro'=>'スチームコンベクションオーブンやiVarioを実際に使う経験から、メニューとオペレーションに合う導入をご提案します。','headings'=>['現在の調理工程を確認','導入機種と運用を検討','実体験をもとにご説明','設置・立ち上げを支援','導入後の活用をフォロー']],
    ];
    $services = load_content('services');
    $existingServiceIds = array_column($services, 'id');
    foreach ($serviceSeeds as $seed) {
        if (in_array($seed['id'], $existingServiceIds, true)) continue;
        $sections = array_map(fn(string $heading): array => ['heading'=>$heading,'body'=>'この内容は管理画面から編集できます。サービスの具体的な流れや強みを掲載してください。','image'=>''], $seed['headings']);
        unset($seed['headings']); $seed['sections'] = $sections; $seed['published'] = true; $services[] = $seed;
    }
    save_content('services', $services);
}

function content_path(string $name): string { return DATA_DIR . '/content/' . preg_replace('/[^a-z0-9_-]/i', '', $name) . '.json'; }
function load_content(string $name): array
{
    $json = @file_get_contents(content_path($name));
    $data = $json ? json_decode($json, true) : [];
    return is_array($data) ? $data : [];
}
function save_content(string $name, array $data): void
{
    $path = content_path($name);
    $temp = $path . '.tmp';
    file_put_contents($temp, json_encode(array_values($data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    rename($temp, $path);
}
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(24)); }
function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('セッションが切れました。前の画面へ戻ってやり直してください。');
    }
}
function admin_config(): array
{
    $path = DATA_DIR . '/admin.json';
    $data = is_file($path) ? json_decode((string)file_get_contents($path), true) : [];
    return is_array($data) ? $data : [];
}
function save_admin(array $data): void { file_put_contents(DATA_DIR . '/admin.json', json_encode($data, JSON_PRETTY_PRINT), LOCK_EX); }
function is_admin(): bool { return !empty($_SESSION['admin']); }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function published(array $items): array { return array_values(array_filter($items, fn($item) => !empty($item['published']))); }
function inquiry_types(): array
{
    return ['開業相談', '厨房設計・厨房機器', '内装・外装工事', '機器の買取・店舗売却', 'その他'];
}
function save_inquiry(array $inquiry): void
{
    $items = load_content('inquiries');
    array_unshift($items, $inquiry);
    save_content('inquiries', array_slice($items, 0, 1000));
}
function notify_inquiry(array $inquiry, string $recipient): bool
{
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) return false;
    $host = preg_replace('/[^a-z0-9.-]/i', '', (string)($_SERVER['HTTP_HOST'] ?? 'prohit-okinawa.com'));
    $subject = '【' . APP_NAME . '】お問い合わせが届きました';
    $body = "Webサイトからお問い合わせが届きました。\n\n"
        . "受付番号: {$inquiry['id']}\n"
        . "相談種別: {$inquiry['type']}\n"
        . "お名前: {$inquiry['name']}\n"
        . "会社・店舗名: {$inquiry['company']}\n"
        . "電話番号: {$inquiry['phone']}\n"
        . "メール: {$inquiry['email']}\n\n"
        . "お問い合わせ内容:\n{$inquiry['message']}\n\n"
        . "受信日時: {$inquiry['created_at']}\n";
    $headers = [
        'From: ' . APP_NAME . ' <no-reply@' . $host . '>',
        'Reply-To: ' . $inquiry['email'],
        'Content-Type: text/plain; charset=UTF-8',
    ];
    return @mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}
function upload_image(string $field, string $current = ''): string
{
    if (empty($_FILES[$field]['tmp_name'])) return $current;
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 6 * 1024 * 1024) throw new RuntimeException('画像は6MB以下にしてください。');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) throw new RuntimeException('JPEG、PNG、WebPのみ利用できます。');
    $name = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) throw new RuntimeException('画像を保存できませんでした。');
    return 'uploads/' . $name;
}

function upload_image_files(string $field, int $limit): array
{
    if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) return [];
    $saved = [];
    $count = count($_FILES[$field]['name']);
    $selectedCount = count(array_filter($_FILES[$field]['name'], fn($name) => (string)$name !== ''));
    if ($selectedCount > $limit) throw new RuntimeException('施工写真は合計10枚までです。');
    for ($index = 0; $index < $count && count($saved) < $limit; $index++) {
        $error = (int)($_FILES[$field]['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('画像のアップロードに失敗しました。');
        $size = (int)($_FILES[$field]['size'][$index] ?? 0);
        $temporary = (string)($_FILES[$field]['tmp_name'][$index] ?? '');
        if ($size < 1 || $size > 6 * 1024 * 1024) throw new RuntimeException('画像は1枚6MB以下にしてください。');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) throw new RuntimeException('JPEG、PNG、WebPのみ利用できます。');
        $name = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($temporary, UPLOAD_DIR . '/' . $name)) throw new RuntimeException('画像を保存できませんでした。');
        $saved[] = 'uploads/' . $name;
    }
    return $saved;
}

function work_images(array $work): array
{
    $images = array_values(array_filter($work['images'] ?? [], 'is_string'));
    if ($images === [] && !empty($work['image'])) $images[] = (string)$work['image'];
    return array_slice($images, 0, 10);
}

boot_app();
