<?php
require __DIR__ . '/lib.php';
if (!is_admin()) {
    http_response_code(403);
    exit;
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
echo json_encode(array_map(
    static fn(array $item): array => ['id' => (string) ($item['id'] ?? ''), 'title' => (string) ($item['title'] ?? '')],
    load_content('services')
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
