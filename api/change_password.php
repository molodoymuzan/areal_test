<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$newPassword = $data['newPassword'];

$stmt = $pdo->prepare("
    SELECT c.id as contact_id, u.role_id 
    FROM contacts c
    JOIN users u ON c.user_id = u.id
    WHERE c.value = ? AND c.type = 'email' AND c.is_login = 1
");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
    exit;
}

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    UPDATE auth SET 
        password_hash = ?, 
        temp_password = NULL, 
        password_change_required = 0 
    WHERE contact_id = ?
");
$stmt->execute([$hash, $user['contact_id']]);

$_SESSION['password_change_required'] = 0;

echo json_encode([
    'success' => true,
    'role_id' => $user['role_id']
]);
?>