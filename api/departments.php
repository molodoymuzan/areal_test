<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            $name = $data['name'];
            $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (?)");
            $stmt->execute([$name]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'update':
            $id = $data['id'];
            $name = $data['name'];
            $stmt = $pdo->prepare("UPDATE departments SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            echo json_encode(['success' => true]);
            break;
            
        case 'delete':
            $id = $data['id'];
            
            // Проверяем, есть ли сотрудники в отделе
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE department_id = ?");
            $check->execute([$id]);
            $count = $check->fetchColumn();
            
            if ($count > 0) {
                echo json_encode(['success' => false, 'error' => 'В отделе есть сотрудники']);
                exit;
            }
            
            // Проверяем, есть ли должности в отделе
            $check = $pdo->prepare("SELECT COUNT(*) FROM positions WHERE department_id = ?");
            $check->execute([$id]);
            $count = $check->fetchColumn();
            
            if ($count > 0) {
                echo json_encode(['success' => false, 'error' => 'В отделе есть должности']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>