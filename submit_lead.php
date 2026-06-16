<?php
header('Content-Type: application/json');
require __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$allowed_budgets = [
    'Under AED 10,000',
    'AED 10,000 – 50,000',
    'AED 50,000 – 100,000',
    'AED 100,000 – 500,000',
    'Above AED 500,000',
];
$allowed_services = ['Hoardings', 'UniPols', 'Digital Hoardings', 'Transit Marketing'];

$source       = trim($_POST['form_source']  ?? '');
$name         = trim($_POST['full_name']    ?? '');
$phone        = trim($_POST['phone']        ?? '');
$email        = trim($_POST['email']        ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$budget       = trim($_POST['budget']       ?? '');
$services_raw = $_POST['services']          ?? [];

if (!$name || !$phone) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Name and phone are required']);
    exit;
}

if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid email address']);
    exit;
}

if ($budget && !in_array($budget, $allowed_budgets, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid budget selection']);
    exit;
}

$services_clean = array_filter(
    (array) $services_raw,
    fn($s) => in_array($s, $allowed_services, true)
);
$services_str = $services_clean ? implode(',', $services_clean) : null;

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

try {
    get_db()->prepare(
        'INSERT INTO leads
         (form_source, full_name, phone, email, company_name, budget, services, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $source,
        $name,
        $phone,
        $email        ?: null,
        $company_name ?: null,
        $budget       ?: null,
        $services_str,
        $ip           ?: null,
    ]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Database error. Please try again.']);
}
