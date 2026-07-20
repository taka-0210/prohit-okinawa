<?php
declare(strict_types=1);

const APP_NAME = 'プロ厨房HIT沖縄';
const DATA_DIR = __DIR__ . '/../storage';
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
    ];
    foreach ($defaults as $name => $value) {
        $path = content_path($name);
        if (!is_file($path)) save_content($name, $value);
    }
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

boot_app();
