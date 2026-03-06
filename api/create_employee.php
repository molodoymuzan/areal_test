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

    $passport_id = null;
    if (!empty($data['passportSeries']) || !empty($data['passportNumber'])) {
        $stmt = $pdo->prepare("INSERT INTO passports (series, number) VALUES (?, ?)");
        $stmt->execute([$data['passportSeries'] ?? '', $data['passportNumber'] ?? '']);
        $passport_id = $pdo->lastInsertId();
    }

    $address_id = null;
    if (!empty($data['city']) || !empty($data['street']) || !empty($data['house'])) {
        $stmt = $pdo->prepare("INSERT INTO addresses (city, street, house, apartment, postal_code) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['city'] ?? '',
            $data['street'] ?? '',
            $data['house'] ?? '',
            $data['apartment'] ?? '',
            $data['postalCode'] ?? ''
        ]);
        $address_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("
        INSERT INTO users 
        (last_name, first_name, middle_name, birth_date, passport_id, address_id, 
         department_id, position_id, salary, hire_date, role_id, status_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
        $data['lastName'],
        $data['firstName'],
        $data['middleName'] ?? '',
        $data['birthDate'] ?? null,
        $passport_id,
        $address_id,
        $data['departmentId'],
        $data['positionId'],
        $data['salary'] ?? 0,
        $data['hireDate'] ?? date('Y-m-d'),
        $data['roleId']
    ]);
    $user_id = $pdo->lastInsertId();

    if (!empty($data['phone'])) {
        $stmt = $pdo->prepare("INSERT INTO contacts (user_id, type, value, is_login) VALUES (?, 'phone', ?, 0)");
        $stmt->execute([$user_id, $data['phone']]);
    }

    $tempPassword = null;
    if (!empty($data['email'])) {
        $is_login = ($data['roleId'] == 2) ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO contacts (user_id, type, value, is_login) VALUES (?, 'email', ?, ?)");
        $stmt->execute([$user_id, $data['email'], $is_login]);
        $contact_id = $pdo->lastInsertId();
        
        if ($data['roleId'] == 2) {
            $tempPassword = generateTempPassword();
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO auth (contact_id, password_hash, temp_password, password_change_required) 
                VALUES (?, ?, ?, 1)
            ");
            $stmt->execute([$contact_id, $hash, $tempPassword]);
        }
    }

    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'user_id' => $user_id,
        'tempPassword' => $tempPassword
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function generateTempPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}
?>