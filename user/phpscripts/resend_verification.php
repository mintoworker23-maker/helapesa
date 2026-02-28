<?php
session_start();
require_once 'config.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure you've run: composer require phpmailer/phpmailer

// Get email from URL
if (!isset($_GET['email']) || !filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = "Invalid or missing email for verification.";
    header("Location: ../login.php");
    exit();
}

$email = trim($_GET['email']);

// Check if user exists and is not verified
$stmt = $conn->prepare("SELECT id, username, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['login_error'] = "Account not found.";
    header("Location: ../login.php");
    exit();
}

$user = $result->fetch_assoc();
if ((int)$user['is_verified'] === 1) {
    $_SESSION['login_error'] = "Your email is already verified.";
    header("Location: ../login.php");
    exit();
}

$userId = $user['id'];
$username = $user['username'];

// Generate new verification token
$token = bin2hex(random_bytes(32));

// Save token in database
$stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
$stmt->bind_param("si", $token, $userId);
$stmt->execute();

// Send email
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.yourdomain.com';  // Replace with your SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your@email.com';       // SMTP username
    $mail->Password   = 'your_password';        // SMTP password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('no-reply@yourdomain.com', 'Earnflow');
    $mail->addAddress($email, $username);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Verify Your Email Address';
    $verificationLink = "https://yourdomain.com/verify.php?token=$token";
    $mail->Body = "
        <h3>Hello, $username</h3>
        <p>Click the link below to verify your email address:</p>
        <a href='$verificationLink'>$verificationLink</a>
        <br><br>
        <p>If you didn't register on Earnflow, please ignore this email.</p>
    ";

    $mail->send();
    $_SESSION['register_success'] = "Verification email has been resent to $email. Please check your inbox.";
    header("Location: ../login.php");
    exit();
} catch (Exception $e) {
    $_SESSION['login_error'] = "Error sending verification email: {$mail->ErrorInfo}";
    header("Location: ../login.php");
    exit();
}