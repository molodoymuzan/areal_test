<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT 
        u.*,
        p.series as passport_series, p.number as passport_number,
        a.city, a.street, a.house, a.apartment, a.postal_code
    FROM users u
    LEFT JOIN passports p ON u.passport_id = p.id
    LEFT JOIN addresses a ON u.address_id = a.id
    WHERE u.id = ?
");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if ($employee) {
    $stmt = $pdo->prepare("
        SELECT type, value 
        FROM contacts 
        WHERE user_id = ?
    ");
    $stmt->execute([$id]);
    $contacts = $stmt->fetchAll();
    
    $employee['phone'] = '';
    $employee['email'] = '';
    foreach ($contacts as $contact) {
        if ($contact['type'] == 'phone') {
            $employee['phone'] = $contact['value'];
        } else if ($contact['type'] == 'email') {
            $employee['email'] = $contact['value'];
        }
    }
    
    $employee['full_name'] = trim($employee['last_name'] . ' ' . $employee['first_name'] . ' ' . $employee['middle_name']);
    
    echo json_encode($employee);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Employee not found']);
}
?>