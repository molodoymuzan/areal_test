<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$fromProfile = isset($data['currentPassword']); 
$email = $data['email'] ?? null;
$currentPassword = $data['currentPassword'] ?? null;
$newPassword = $data['newPassword'];
$confirmPassword = $data['confirmPassword'] ?? $data['newPassword'];

try {
    if ($fromProfile) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("
            SELECT c.id as contact_id, a.password_hash 
            FROM contacts c
            JOIN auth a ON c.id = a.contact_id
            WHERE c.user_id = ? AND c.type = 'email' AND c.is_login = 1
        ");
        $stmt->execute([$user_id]);
        $authData = $stmt->fetch();

        if (!$authData) {
            echo json_encode(['success' => false, 'error' => 'Аутентификационные данные не найдены']);
            exit;
        }

        if (!password_verify($currentPassword, $authData['password_hash'])) {
            echo json_encode(['success' => false, 'error' => 'Неверный текущий пароль']);
            exit;
        }
    } else {
        if (!$email) {
            echo json_encode(['success' => false, 'error' => 'Email не указан']);
            exit;
        }

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

        $contact_id = $user['contact_id'];
        $role_id = $user['role_id'];
    }

    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'error' => 'Пароль должен быть минимум 8 символов']);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'Пароли не совпадают']);
        exit;
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    if ($fromProfile) {
        $stmt = $pdo->prepare("UPDATE auth SET password_hash = ? WHERE contact_id = ?");
        $stmt->execute([$hash, $authData['contact_id']]);
        
        $_SESSION['password_change_required'] = 0;
        
        echo json_encode(['success' => true]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE auth SET 
                password_hash = ?, 
                temp_password = NULL, 
                password_change_required = 0 
            WHERE contact_id = ?
        ");
        $stmt->execute([$hash, $contact_id]);

        $_SESSION['password_change_required'] = 0;

        echo json_encode([
            'success' => true,
            'role_id' => $role_id
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
?>