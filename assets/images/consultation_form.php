<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to      = "connecttocontink@gmail.com";
    $subject = "New Consultation Request — Rabtora Landing Page";

    $fullName = $_POST['fullName'] ?? '';
    $phone    = $_POST['phone']    ?? '';
    $email    = $_POST['email']    ?? '';
    $goal     = $_POST['goal']     ?? '';
    $otherGoal= $_POST['otherGoal']?? '';
    $company  = $_POST['company']  ?? '';
    $message  = $_POST['message']  ?? '';

    if ($goal === "Other" && !empty($otherGoal)) {
        $goal .= " — " . $otherGoal;
    }

    $headers  = "From: $email\r\n";
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
