<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$department_filter = isset($_GET['department']) && $_GET['department'] !== 'all' ? "AND u.department_id = " . intval($_GET['department']) : "";
$position_filter = isset($_GET['position']) && $_GET['position'] !== 'all' ? "AND u.position_id = " . intval($_GET['position']) : "";
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
    WHERE u.role_id = 3 $department_filter $position_filter $search_filter
";

$stmt = $pdo->query($query);
$employees = $stmt->fetchAll();

foreach ($employees as &$employee) {
    $stmt = $pdo->prepare("
        SELECT type, value 
        FROM contacts 
        WHERE user_id = ?
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

echo json_encode($employees);
?>