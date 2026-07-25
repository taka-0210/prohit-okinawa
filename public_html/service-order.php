<?php
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['ok'=>false, 'message'=>'ログインが必要です。'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false, 'message'=>'POSTで送信してください。'], JSON_UNESCAPED_UNICODE);
    exit;
}
$payload = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($payload) || !hash_equals($_SESSION['csrf'] ?? '', (string)($payload['csrf'] ?? ''))) {
    http_response_code(419);
    echo json_encode(['ok'=>false, 'message'=>'セッションが切れました。'], JSON_UNESCAPED_UNICODE);
    exit;
}
$items = load_content('services');
$order = array_values(array_unique(array_map('strval', (array)($payload['order'] ?? []))));
$lookup = [];
foreach ($items as $item) $lookup[(string)($item['id'] ?? '')] = $item;
if (count($order) !== count($items) || array_diff(array_keys($lookup), $order) !== [] || array_diff($order, array_keys($lookup)) !== []) {
    http_response_code(422);
    echo json_encode(['ok'=>false, 'message'=>'サービス一覧を再読み込みして、もう一度並べ替えてください。'], JSON_UNESCAPED_UNICODE);
    exit;
}
$sorted = [];
foreach ($order as $id) $sorted[] = $lookup[$id];
save_content('services', $sorted);
echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
