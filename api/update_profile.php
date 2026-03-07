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
$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE users SET 
            last_name = ?,
            first_name = ?,
            middle_name = ?,
            birth_date = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['lastName'] ?? '',
        $data['firstName'] ?? '',
        $data['middleName'] ?? '',
        $data['birthDate'] ?? null,
        $user_id
    ]);

    if (!empty($data['passportSeries']) || !empty($data['passportNumber'])) {
        $stmt = $pdo->prepare("SELECT passport_id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user['passport_id']) {
            $stmt = $pdo->prepare("UPDATE passports SET series = ?, number = ? WHERE id = ?");
            $stmt->execute([$data['passportSeries'] ?? '', $data['passportNumber'] ?? '', $user['passport_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO passports (series, number) VALUES (?, ?)");
            $stmt->execute([$data['passportSeries'] ?? '', $data['passportNumber'] ?? '']);
            $passport_id = $pdo->lastInsertId();
            $pdo->prepare("UPDATE users SET passport_id = ? WHERE id = ?")->execute([$passport_id, $user_id]);
        }
    }

    if (!empty($data['city']) || !empty($data['street']) || !empty($data['house'])) {
        $stmt = $pdo->prepare("SELECT address_id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user['address_id']) {
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
                $user['address_id']
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO addresses (city, street, house, apartment, postal_code) 
                VALUES (?, ?, ?, ?, ?)
            ");
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
    }

    if (!empty($data['phone'])) {
        $stmt = $pdo->prepare("SELECT id FROM contacts WHERE user_id = ? AND type = 'phone'");
        $stmt->execute([$user_id]);
        $phoneContact = $stmt->fetch();

        if ($phoneContact) {
            $stmt = $pdo->prepare("UPDATE contacts SET value = ? WHERE id = ?");
            $stmt->execute([$data['phone'], $phoneContact['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO contacts (user_id, type, value, is_login) VALUES (?, 'phone', ?, 0)");
            $stmt->execute([$user_id, $data['phone']]);
        }
    }

    if (!empty($data['email'])) {
        $stmt = $pdo->prepare("SELECT id, value FROM contacts WHERE user_id = ? AND type = 'email' AND is_login = 1");
        $stmt->execute([$user_id]);
        $emailContact = $stmt->fetch();

        if ($emailContact && $emailContact['value'] !== $data['email']) {
            $check = $pdo->prepare("SELECT id FROM contacts WHERE value = ? AND type = 'email' AND user_id != ?");
            $check->execute([$data['email'], $user_id]);
            if ($check->fetch()) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'Этот email уже используется']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE contacts SET value = ? WHERE id = ?");
            $stmt->execute([$data['email'], $emailContact['id']]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>