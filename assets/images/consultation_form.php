<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to      = "connecttocontink@gmail.com";
    $subject = "New Consultation Request — Rabtora Landing Page";

    $fullName = trim($_POST['fullName'] ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $email    = trim($_POST['email']    ?? '');
    $goal     = trim($_POST['goal']     ?? '');
    $company  = trim($_POST['company']  ?? '');
    $message  = trim($_POST['message']  ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.";
        exit;
    }
    $email = preg_replace('/[\r\n]/', '', $email);

    $headers  = "From: noreply@rabtora.ae\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $body  = "New Consultation Request\n";
    $body .= str_repeat("=", 40) . "\n\n";
    $body .= "Name:    $fullName\n";
    $body .= "Phone:   $phone\n";
    $body .= "Email:   $email\n";
    $body .= "Company: $company\n";
    $body .= "Service: $goal\n";
    if (!empty($message)) {
        $body .= "\nMessage:\n$message\n";
    }

    if (mail($to, $subject, $body, $headers)) {
        echo "Mail sent successfully.";
    } else {
        echo "Mail sending failed.";
    }
} else {
    echo "Invalid request method.";
}
?>
