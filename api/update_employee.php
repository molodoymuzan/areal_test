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
$user_id = $data['id'];

try {
    $pdo->beginTransaction();

    $user = $pdo->prepare("SELECT passport_id, address_id FROM users WHERE id = ?");
    $user->execute([$user_id]);
    $userData = $user->fetch();

    if ($userData['passport_id']) {
        $stmt = $pdo->prepare("UPDATE passports SET series = ?, number = ? WHERE id = ?");
        $stmt->execute([$data['passportSeries'], $data['passportNumber'], $userData['passport_id']]);
    }

    if ($userData['address_id']) {
        $stmt = $pdo->prepare("
            UPDATE addresses SET 
                city = ?, street = ?, house = ?, apartment = ?, postal_code = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $data['city'], $data['street'], $data['house'],
            $data['apartment'], $data['postalCode'], $userData['address_id']
        ]);
    }

    $stmt = $pdo->prepare("
        UPDATE users SET 
            last_name = ?, first_name = ?, middle_name = ?,
            birth_date = ?, hire_date = ?, salary = ?,
            department_id = ?, position_id = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['lastName'], $data['firstName'], $data['middleName'],
        $data['birthDate'], $data['hireDate'], $data['salary'],
        $data['departmentId'], $data['positionId'],
        $user_id
    ]);

    $pdo->prepare("DELETE FROM user_contacts WHERE user_id = ?")->execute([$user_id]);
    
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