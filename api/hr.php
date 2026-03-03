<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$status_filter = "";
if (isset($_GET['status']) && $_GET['status'] !== 'all') {
    if ($_GET['status'] == 'active') {
        $status_filter = "AND u.status_id = 1";
    } else if ($_GET['status'] == 'dismissed') {
        $status_filter = "AND u.status_id = 2";
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$search_filter = "";
if ($search) {
    $search_filter = "AND CONCAT(u.last_name, ' ', u.first_name, ' ', COALESCE(u.middle_name, '')) LIKE '%" . addslashes($search) . "%'";
}

$query = "
    SELECT 
        u.id,
        CONCAT(u.last_name, ' ', u.first_name, ' ', COALESCE(u.middle_name, '')) as full_name,
        u.last_name, u.first_name, u.middle_name,
        u.birth_date, u.hire_date, u.salary,
        u.department_id, u.position_id, u.status_id,
        d.name as department_name,
        p.name as position_name,
        pass.series as passport_series,
        pass.number as passport_number,
        addr.city, addr.street, addr.house, addr.apartment, addr.postal_code
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN positions p ON u.position_id = p.id
    LEFT JOIN passports pass ON u.passport_id = pass.id
    LEFT JOIN addresses addr ON u.address_id = addr.id
    WHERE u.role_id = 2 $status_filter $search_filter
";

$stmt = $pdo->query($query);
$hr = $stmt->fetchAll();

foreach ($hr as &$employee) {
    $stmt = $pdo->prepare("
        SELECT c.type, c.value
        FROM contacts c
        JOIN user_contacts uc ON c.id = uc.contact_id
        WHERE uc.user_id = ?
    ");
    $stmt->execute([$employee['id']]);
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
}

echo json_encode($hr);
?>