<?php
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
if (!is_admin()) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}
$items = array_map(function (array $item): array {
    $image = (string)($item['image'] ?? '');
    return [
        'id' => (string)($item['id'] ?? ''),
        'title' => (string)($item['title'] ?? ''),
        'image' => $image,
        'exists' => $image !== '' && is_file(__DIR__ . '/' . $image),
        'published' => !empty($item['published']),
    ];
}, load_content('hero'));
echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
