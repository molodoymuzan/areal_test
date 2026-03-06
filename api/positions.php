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
            $department_id = $data['department_id'];
            $stmt = $pdo->prepare("INSERT INTO positions (name, department_id) VALUES (?, ?)");
            $stmt->execute([$name, $department_id]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'update':
            $id = $data['id'];
            $name = $data['name'];
            $department_id = $data['department_id'];
            $stmt = $pdo->prepare("UPDATE positions SET name = ?, department_id = ? WHERE id = ?");
            $stmt->execute([$name, $department_id, $id]);
            echo json_encode(['success' => true]);
            break;
            
        case 'delete':
            $id = $data['id'];
            
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE position_id = ?");
            $check->execute([$id]);
            $count = $check->fetchColumn();
            
            if ($count > 0) {
                echo json_encode(['success' => false, 'error' => 'На этой должности есть сотрудники']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
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