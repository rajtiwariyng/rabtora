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
$name         = trim($_POST['full_name']    ?? $_POST['name'] ?? '');
$phone        = trim($_POST['phone']        ?? '');
$email        = trim($_POST['email']        ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$budget       = trim($_POST['budget']       ?? '');
$message      = trim($_POST['message']      ?? '');

if (!$name) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Name is required']);
    exit;
}

// contact_form requires email; other forms require phone
if ($source === 'contact_form') {
    if (!$email) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Email is required']);
        exit;
    }
} else {
    if (!$phone) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Name and phone are required']);
        exit;
    }
}

if (mb_strlen($name) > 255 || mb_strlen($phone) > 50 || mb_strlen($company_name) > 255) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Input too long']);
    exit;
}

if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid email address']);
    exit;
}

if ($source !== 'contact_form' && $budget && !in_array($budget, $allowed_budgets, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid budget selection']);
    exit;
}

if ($source === 'contact_form') {
    // Single service value from contact form select
    $services_str = ($v = trim($_POST['service'] ?? '')) !== '' ? $v : null;
} else {
    $services_raw   = $_POST['services'] ?? [];
    $services_clean = array_filter(
        (array) $services_raw,
        fn($s) => in_array($s, $allowed_services, true)
    );
    $services_str = $services_clean ? implode(',', $services_clean) : null;
}

$ip_raw = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ip     = filter_var(trim(explode(',', $ip_raw)[0]), FILTER_VALIDATE_IP) ?: null;

try {
    get_db()->prepare(
        'INSERT INTO leads
         (form_source, full_name, phone, email, company_name, budget, services, message, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $source,
        $name,
        $phone        ?: null,
        $email        ?: null,
        $company_name ?: null,
        $budget       ?: null,
        $services_str,
        $message      ?: null,
        $ip           ?: null,
    ]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    error_log('submit_lead insert failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Database error. Please try again.']);
}
