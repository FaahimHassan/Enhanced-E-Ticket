<?php
require_once __DIR__ . '/../controller/common.php';
require_once __DIR__ . '/../model/overviewModel.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
$user = current_user();
if (!$user || !in_array($user['role'], ['admin', 'provider'], true)) {
    http_response_code($user ? 403 : 401);
    echo json_encode(['error' => 'Please log in with an active admin or provider account.']);
    exit;
}
session_write_close();
try {
    echo json_encode(operations_overview($user));
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not load the latest overview. Please try again.']);
}
