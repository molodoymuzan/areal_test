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

if (!empty($data['email'])) {
    $oldEmail = $pdo->prepare("SELECT id, value FROM contacts WHERE user_id = ? AND type = 'email'");
    $oldEmail->execute([$user_id]);
    $oldEmailData = $oldEmail->fetch();
    
    if (!$oldEmailData || $oldEmailData['value'] !== $data['email']) {
        $checkEmail = $pdo->prepare("SELECT id FROM contacts WHERE value = ? AND type = 'email' AND user_id != ?");
        $checkEmail->execute([$data['email'], $user_id]);
        if ($checkEmail->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Этот email уже используется']);
            exit;
        }
    }
}

try {
    $pdo->beginTransaction();

    $user = $pdo->prepare("SELECT passport_id, address_id, role_id FROM users WHERE id = ?");
    $user->execute([$user_id]);
    $userData = $user->fetch();

    $oldEmail = $pdo->prepare("SELECT id, value FROM contacts WHERE user_id = ? AND type = 'email'");
    $oldEmail->execute([$user_id]);
    $oldEmailData = $oldEmail->fetch();

    if ($userData['passport_id']) {
        $stmt = $pdo->prepare("UPDATE passports SET series = ?, number = ? WHERE id = ?");
        $stmt->execute([$data['passportSeries'] ?? '', $data['passportNumber'] ?? '', $userData['passport_id']]);
    } else if (!empty($data['passportSeries']) || !empty($data['passportNumber'])) {
        $stmt = $pdo->prepare("INSERT INTO passports (series, number) VALUES (?, ?)");
        $stmt->execute([$data['passportSeries'] ?? '', $data['passportNumber'] ?? '']);
        $passport_id = $pdo->lastInsertId();
        $pdo->prepare("UPDATE users SET passport_id = ? WHERE id = ?")->execute([$passport_id, $user_id]);
    }

    if ($userData['address_id']) {
        $stmt = $pdo->prepare("
            UPDATE addresses SET 
                city = ?, street = ?, house = ?, apartment = ?, postal_code = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $data['city'] ?? '',
            $data['street'] ?? '',
            $data['house'] ?? '',
            $data['apartment'] ?? '',
            $data['postalCode'] ?? '',
            $userData['address_id']
        ]);
    } else if (!empty($data['city']) || !empty($data['street']) || !empty($data['house'])) {
        $stmt = $pdo->prepare("INSERT INTO addresses (city, street, house, apartment, postal_code) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['city'] ?? '',
            $data['street'] ?? '',
            $data['house'] ?? '',
            $data['apartment'] ?? '',
            $data['postalCode'] ?? ''
        ]);
        $address_id = $pdo->lastInsertId();
        $pdo->prepare("UPDATE users SET address_id = ? WHERE id = ?")->execute([$address_id, $user_id]);
    }

    $stmt = $pdo->prepare("
        UPDATE users SET 
            last_name = ?, first_name = ?, middle_name = ?,
            birth_date = ?, hire_date = ?, salary = ?,
            department_id = ?, position_id = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['lastName'],
        $data['firstName'],
        $data['middleName'] ?? '',
        $data['birthDate'] ?? null,
        $data['hireDate'] ?? null,
        $data['salary'] ?? 0,
        $data['departmentId'],
        $data['positionId'],
        $user_id
    ]);

    $pdo->prepare("DELETE FROM contacts WHERE user_id = ? AND type = 'phone'")->execute([$user_id]);
    if (!empty($data['phone'])) {
        $stmt = $pdo->prepare("INSERT INTO contacts (user_id, type, value, is_login) VALUES (?, 'phone', ?, 0)");
        $stmt->execute([$user_id, $data['phone']]);
    }

    if (!empty($data['email'])) {
        if ($oldEmailData && $oldEmailData['value'] !== $data['email']) {
            $stmt = $pdo->prepare("UPDATE contacts SET value = ? WHERE id = ?");
            $stmt->execute([$data['email'], $oldEmailData['id']]);
        } else if (!$oldEmailData) {
            $is_login = ($data['roleId'] == 2) ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO contacts (user_id, type, value, is_login) VALUES (?, 'email', ?, ?)");
            $stmt->execute([$user_id, $data['email'], $is_login]);
            
            if ($data['roleId'] == 2) {
                $contact_id = $pdo->lastInsertId();
                $tempPassword = generateTempPassword();
                $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO auth (contact_id, password_hash, temp_password, password_change_required) 
                    VALUES (?, ?, ?, 1)
                ");
                $stmt->execute([$contact_id, $hash, $tempPassword]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

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