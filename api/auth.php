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
        SELECT 
            c.user_id,
            c.id as contact_id,
            a.password_hash,
            a.temp_password,
            a.password_change_required,
            u.last_name,
            u.first_name,
            u.middle_name,
            u.role_id
        FROM contacts c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN auth a ON c.id = a.contact_id
        WHERE c.value = ? AND c.type = 'email' AND c.is_login = 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $stmt = $pdo->prepare("UPDATE auth SET last_login = NOW() WHERE contact_id = ?");
        $stmt->execute([$user['contact_id']]);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['user_name'] = trim($user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']);
        $_SESSION['password_change_required'] = $user['password_change_required'];

        echo json_encode([
            'success' => true,
            'role_id' => $user['role_id'],
            'password_change_required' => $user['password_change_required']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>