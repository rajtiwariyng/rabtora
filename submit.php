<?php
/**
 * Rabtora Madhyam Management — contact form handler.
 * Receives the landing-page form (name, email, mobile, service) and
 * stores it in the MySQL `enquiries` table (see schema.sql).
 *
 * Responds with JSON when called via fetch/AJAX, otherwise shows a
 * simple HTML thank-you page (graceful no-JS fallback).
 */

declare(strict_types=1);

/* ----------------------------------------------------------------------
 * 1. DATABASE CONFIG  — update these for your hosting environment.
 * -------------------------------------------------------------------- */
$DB_HOST = 'localhost';
$DB_NAME = 'rabtora';
$DB_USER = 'rabtora_user';      // <-- change
$DB_PASS = 'CHANGE_ME';         // <-- change
$NOTIFY_EMAIL = 'hello@rabtora.ae'; // optional: where to email new leads ('' to disable)

/* ----------------------------------------------------------------------
 * 2. Helpers
 * -------------------------------------------------------------------- */
function wants_json(): bool {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xrw    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return str_contains($accept, 'application/json') || strtolower($xrw) === 'xmlhttprequest';
}

function respond(int $code, bool $ok, string $message): void {
    http_response_code($code);
    if (wants_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $message]);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo "<!doctype html><meta charset='utf-8'><title>Rabtora</title>"
           . "<body style='font-family:sans-serif;background:#08142f;color:#f7f7f5;text-align:center;padding:80px'>"
           . ($ok
                ? "<h1>Thank you</h1><p>$safe</p>"
                : "<h1>Sorry</h1><p>$safe</p>")
           . "<p><a style='color:#c89b2c' href='index.html'>&larr; Back to site</a></p></body>";
    }
    exit;
}

/* ----------------------------------------------------------------------
 * 3. Validate request
 * -------------------------------------------------------------------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, false, 'Method not allowed.');
}

// Honeypot: real users never fill the hidden "company" field.
if (!empty(trim((string)($_POST['company'] ?? '')))) {
    respond(200, true, 'Thank you.'); // silently accept & drop spam
}

$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$mobile  = trim((string)($_POST['mobile'] ?? ''));
$service = trim((string)($_POST['service'] ?? ''));
$message = trim((string)($_POST['message'] ?? '')); // optional, not on the form yet

$errors = [];
if ($name === '' || mb_strlen($name) > 120)              $errors[] = 'a valid name';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))          $errors[] = 'a valid email';
if (!preg_match('/^[0-9+()\s-]{7,40}$/', $mobile))       $errors[] = 'a valid mobile number';
if ($service === '' || mb_strlen($service) > 60)         $errors[] = 'a service';

if ($errors) {
    respond(422, false, 'Please provide ' . implode(', ', $errors) . '.');
}

/* ----------------------------------------------------------------------
 * 4. Store in MySQL (prepared statement = no SQL injection)
 * -------------------------------------------------------------------- */
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    $stmt = $pdo->prepare(
        'INSERT INTO enquiries (name, email, mobile, service, message, ip_address, user_agent)
         VALUES (:name, :email, :mobile, :service, :message, :ip, :ua)'
    );
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':mobile'  => $mobile,
        ':service' => $service,
        ':message' => $message !== '' ? $message : null,
        ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
        ':ua'      => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
} catch (Throwable $e) {
    error_log('Rabtora enquiry insert failed: ' . $e->getMessage());
    respond(500, false, 'We could not save your request right now. Please email ' . $NOTIFY_EMAIL . '.');
}

/* ----------------------------------------------------------------------
 * 5. Optional: notify the team by email
 * -------------------------------------------------------------------- */
if ($NOTIFY_EMAIL !== '') {
    $subject = 'New website enquiry — ' . $service;
    $body    = "Name: $name\nEmail: $email\nMobile: $mobile\nService: $service\n"
             . ($message !== '' ? "Message: $message\n" : '')
             . 'Time: ' . date('Y-m-d H:i:s') . "\n";
    $headers = "From: website@rabtora.ae\r\nReply-To: $email\r\n";
    @mail($NOTIFY_EMAIL, $subject, $body, $headers); // silent if mail() not configured
}

respond(200, true, 'Your request has been received — a Rabtora strategist will reach out shortly.');
