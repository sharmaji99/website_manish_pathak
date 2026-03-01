<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);

if (!is_array($payload)) {
    $payload = $_POST;
}

$fullName = trim((string)($payload['name'] ?? $payload['full_name'] ?? ''));
$mobileNumber = preg_replace('/\D+/', '', (string)($payload['phone'] ?? $payload['mobile_number'] ?? ''));
$requirement = trim((string)($payload['message'] ?? $payload['requirement'] ?? ''));

if ($fullName === '' || mb_strlen($fullName) > 120) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid full name.']);
    exit;
}

if (!preg_match('/^[6-9][0-9]{9}$/', $mobileNumber)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid mobile number.']);
    exit;
}

if ($requirement === '' || mb_strlen($requirement) > 2000) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please enter your requirement.']);
    exit;
}

try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO consultations (full_name, mobile_number, requirement) VALUES (:full_name, :mobile_number, :requirement)'
    );

    $stmt->execute([
        ':full_name' => $fullName,
        ':mobile_number' => $mobileNumber,
        ':requirement' => $requirement,
    ]);

    http_response_code(201);
    echo json_encode(['ok' => true, 'message' => 'Consultation submitted successfully.']);
} catch (Throwable $exception) {
    error_log('[submit-consultation] ' . $exception->getMessage());

    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Unable to submit right now. Please try again later.']);
}
