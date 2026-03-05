<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO passports (series, number) VALUES (?, ?)");
    $stmt->execute([$data['passportSeries'], $data['passportNumber']]);
    $passport_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO addresses (city, street, house, apartment, postal_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data['city'], $data['street'], $data['house'], $data['apartment'], $data['postalCode']]);
    $address_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO users 
        (last_name, first_name, middle_name, birth_date, passport_id, address_id, 
         department_id, position_id, salary, hire_date, role_id, status_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
        $data['lastName'], $data['firstName'], $data['middleName'],
        $data['birthDate'], $passport_id, $address_id,
        $data['departmentId'], $data['positionId'], $data['salary'],
        $data['hireDate'], $data['roleId']
    ]);
    $user_id = $pdo->lastInsertId();

    if (!empty($data['phone'])) {
        $stmt = $pdo->prepare("INSERT INTO contacts (type, value) VALUES ('phone', ?)");
        $stmt->execute([$data['phone']]);
        $contact_id = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO user_contacts (user_id, contact_id) VALUES (?, ?)")->execute([$user_id, $contact_id]);
    }

    if (!empty($data['email'])) {
        $stmt = $pdo->prepare("INSERT INTO contacts (type, value) VALUES ('email', ?)");
        $stmt->execute([$data['email']]);
        $contact_id = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO user_contacts (user_id, contact_id) VALUES (?, ?)")->execute([$user_id, $contact_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>