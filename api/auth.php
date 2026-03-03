<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT a.*, u.last_name, u.first_name, u.middle_name, u.role_id 
        FROM auth a
        JOIN users u ON a.user_id = u.id
        WHERE a.login_email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password_hash']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['user_name'] = trim($user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']);

        echo json_encode([
            'success' => true,
            'role_id' => $user['role_id']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>