<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "connecttocontink@gmail.com"; // Replace with your email
    $subject = "New Branding Consultation Form Submission";

    // Collect form inputs
    $fullName = $_POST['fullName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $goal = $_POST['goal'] ?? '';
    $otherGoal = $_POST['otherGoal'] ?? '';

    // If "Other" selected, append the custom goal
    if ($goal === "Other" && !empty($otherGoal)) {
        $goal .= " - " . $otherGoal;
    }

    // Email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Email body
    $body = "New Branding Consultation Request\n\n";
    $body .= "Name: $fullName\n";
    $body .= "Phone: $phone\n";
    $body .= "Email: $email\n";
    $body .= "Primary Goal: $goal\n";

    // Send email
    if (mail($to, $subject, $body, $headers)) {
        echo "Mail sent successfully.";
    } else {
        echo "Mail sending failed.";
    }
} else {
    echo "Invalid request method.";
}
?>
