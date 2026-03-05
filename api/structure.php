<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$departments = $pdo->query("
    SELECT 
        d.*,
        COUNT(u.id) as employees_count
    FROM departments d
    LEFT JOIN users u ON u.department_id = d.id AND u.role_id IN (2,3) AND u.status_id = 1
    GROUP BY d.id
    ORDER BY d.name
")->fetchAll();

$positions = $pdo->query("
    SELECT p.*, d.name as department_name 
    FROM positions p
    JOIN departments d ON p.department_id = d.id
    ORDER BY d.name, p.name
")->fetchAll();

echo json_encode([
    'departments' => $departments,
    'positions' => $positions
]);
?>