<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        u.*,
        a.city, a.street, a.house, a.apartment, a.postal_code,
        d.name as department_name,
        p.name as position_name
    FROM users u
    LEFT JOIN addresses a ON u.address_id = a.id
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN positions p ON u.position_id = p.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $stmt = $pdo->prepare("
        SELECT c.type, c.value
        FROM contacts c
        JOIN user_contacts uc ON c.id = uc.contact_id
        WHERE uc.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $contacts = $stmt->fetchAll();
    
    foreach ($contacts as $contact) {
        if ($contact['type'] == 'phone') {
            $user['phone'] = $contact['value'];
        } else if ($contact['type'] == 'email') {
            $user['email'] = $contact['value'];
        }
    }
    
    $user['full_name'] = trim($user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name']);
    
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}
?>