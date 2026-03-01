<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

allowCorsForKnownOrigin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(405, ['ok' => false, 'message' => 'Method Not Allowed']);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);

if (!is_array($payload)) {
    sendJsonResponse(400, ['ok' => false, 'message' => 'Invalid JSON payload.']);
}

$name = trim((string)($payload['name'] ?? ''));
$phone = preg_replace('/\s+/', '', (string)($payload['phone'] ?? ''));
$message = trim((string)($payload['message'] ?? ''));

if ($name === '' || mb_strlen($name) > CONTACT_MAX_NAME_LENGTH) {
    sendJsonResponse(422, ['ok' => false, 'message' => 'Name is missing or too long.']);
}

if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
    sendJsonResponse(422, ['ok' => false, 'message' => 'Phone number format is invalid.']);
}

if ($message === '' || mb_strlen($message) > CONTACT_MAX_MESSAGE_LENGTH) {
    sendJsonResponse(422, ['ok' => false, 'message' => 'Message is missing or too long.']);
}

$userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

try {
    $pdo = getDatabaseConnection();

    $rateLimitStmt = $pdo->prepare(
        'SELECT submitted_at
         FROM contact_submissions
         WHERE phone = :phone
         ORDER BY id DESC
         LIMIT 1'
    );
    $rateLimitStmt->execute([':phone' => $phone]);
    $lastSubmission = $rateLimitStmt->fetch();

    if (is_array($lastSubmission) && isset($lastSubmission['submitted_at'])) {
        $lastTimestamp = strtotime((string)$lastSubmission['submitted_at']);
        if ($lastTimestamp !== false && (time() - $lastTimestamp) < CONTACT_RATE_LIMIT_SECONDS) {
            sendJsonResponse(429, ['ok' => false, 'message' => 'Please wait before sending another message.']);
        }
    }

    $insertStmt = $pdo->prepare(
        'INSERT INTO contact_submissions
        (name, phone, message, source_page, ip_address, user_agent)
        VALUES
        (:name, :phone, :message, :source_page, :ip_address, :user_agent)'
    );

    $insertStmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':message' => $message,
        ':source_page' => 'index-contact-form',
        ':ip_address' => $userIp,
        ':user_agent' => $userAgent,
    ]);

    sendJsonResponse(201, ['ok' => true, 'message' => 'Message received successfully.']);
} catch (Throwable $e) {
    error_log('[contact-submit] ' . $e->getMessage());

    if (APP_ENV !== 'production') {
        sendJsonResponse(500, ['ok' => false, 'message' => 'Server error', 'debug' => $e->getMessage()]);
    }

    sendJsonResponse(500, ['ok' => false, 'message' => 'Server error. Please try again later.']);
}
