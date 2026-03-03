<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

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