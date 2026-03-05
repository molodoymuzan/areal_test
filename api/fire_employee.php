<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['id'];

$stmt = $pdo->prepare("UPDATE users SET status_id = 2 WHERE id = ?");
$stmt->execute([$user_id]);

echo json_encode(['success' => true]);
?>