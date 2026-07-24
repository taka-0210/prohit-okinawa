<?php
declare(strict_types=1);

require __DIR__ . '/lib.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=300, stale-while-revalidate=900');

const INSTAGRAM_FEED_URL = 'https://pro-chubo.com/instagram/get_instagram_okinawa.php';
const INSTAGRAM_PROFILE_URL = 'https://pro-chubo.com/instagram/get_profile_okinawa.php';
const INSTAGRAM_CACHE_SECONDS = 900;

$cachePath = DATA_DIR . '/instagram-okinawa-cache.json';
$cached = read_instagram_cache($cachePath);

if ($cached !== null && time() - (int)($cached['cached_at'] ?? 0) < INSTAGRAM_CACHE_SECONDS) {
    send_instagram_json($cached);
}

try {
    $feed = fetch_instagram_json(INSTAGRAM_FEED_URL);
    $profile = [];
    try {
        $profile = fetch_instagram_json(INSTAGRAM_PROFILE_URL);
    } catch (Throwable $profileException) {
        // 投稿一覧が取得できる場合は、プロフィール画像だけ既定ロゴのまま表示します。
    }
    $payload = [
        'data' => sanitize_instagram_posts($feed['data'] ?? []),
        'profile_picture_url' => sanitize_http_url($profile['profile_picture_url'] ?? ''),
        'cached_at' => time(),
    ];
    if ($payload['data'] === []) {
        throw new RuntimeException('Instagram feed is empty.');
    }
    file_put_contents($cachePath, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    send_instagram_json($payload);
} catch (Throwable $exception) {
    if ($cached !== null) {
        send_instagram_json($cached);
    }
    http_response_code(502);
    send_instagram_json(['data' => [], 'profile_picture_url' => '', 'error' => 'Instagramの投稿を取得できませんでした。']);
}

function fetch_instagram_json(string $url): array
{
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'ProChuboHitOkinawa/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if (!is_string($body) || $status < 200 || $status >= 300) {
            throw new RuntimeException('Instagram API request failed.');
        }
    } else {
        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "Accept: application/json\r\nUser-Agent: ProChuboHitOkinawa/1.0\r\n",
        ]]);
        $body = @file_get_contents($url, false, $context);
        if (!is_string($body)) {
            throw new RuntimeException('Instagram API request failed.');
        }
    }
    $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    return is_array($decoded) ? $decoded : [];
}

function sanitize_instagram_posts(mixed $posts): array
{
    if (!is_array($posts)) {
        return [];
    }
    $clean = [];
    foreach (array_slice($posts, 0, 12) as $post) {
        if (!is_array($post)) {
            continue;
        }
        $type = strtoupper((string)($post['media_type'] ?? 'IMAGE'));
        $mediaUrl = sanitize_http_url($post['media_url'] ?? '');
        $permalink = sanitize_http_url($post['permalink'] ?? '');
        if (!in_array($type, ['IMAGE', 'VIDEO', 'CAROUSEL_ALBUM'], true) || $mediaUrl === '' || $permalink === '') {
            continue;
        }
        $clean[] = [
            'media_type' => $type,
            'media_url' => $mediaUrl,
            'thumbnail_url' => sanitize_http_url($post['thumbnail_url'] ?? ''),
            'permalink' => $permalink,
            'like_count' => max(0, (int)($post['like_count'] ?? 0)),
            'timestamp' => (string)($post['timestamp'] ?? ''),
        ];
    }
    return $clean;
}

function sanitize_http_url(mixed $url): string
{
    $url = filter_var((string)$url, FILTER_VALIDATE_URL);
    if ($url === false || !in_array(strtolower((string)parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
        return '';
    }
    return $url;
}

function read_instagram_cache(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }
    $decoded = json_decode((string)file_get_contents($path), true);
    return is_array($decoded) && isset($decoded['data']) ? $decoded : null;
}

function send_instagram_json(array $payload): never
{
    unset($payload['cached_at']);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
