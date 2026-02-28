<?php
session_start();
require_once '../phpscripts/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['whatsapp_error'] = "You must be logged in to submit proof.";
    header("Location: ../dashboard/socialmedia.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['screenshot']) || !isset($_POST['promo_id'])) {
    $_SESSION['whatsapp_error'] = "Invalid submission.";
    header("Location: ../dashboard/socialmedia.php");
    exit();
}

$promo_id = intval($_POST['promo_id']);
$screenshot = $_FILES['screenshot'];

// Absolute file system path
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/admin.earnflowservices.com/uploads/whatsapp/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Validate image
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($screenshot['type'], $allowedTypes)) {
    $_SESSION['whatsapp_error'] = "Only JPG, PNG, or WEBP images are allowed.";
    header("Location: ../dashboard/socialmedia.php");
    exit();
}

// Generate unique filename
$extension = pathinfo($screenshot['name'], PATHINFO_EXTENSION);
$filename = 'screenshot_' . time() . '_' . uniqid() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($screenshot['tmp_name'], $filepath)) {
    $_SESSION['whatsapp_error'] = "Failed to upload image. Try again.";
    header("Location: ../dashboard/socialmedia.php");
    exit();
}

// Save PUBLIC URL to DB
$imageUrl = 'https://earnflowservices.com/admin.earnflowservices.com/uploads/whatsapp/' . $filename;

$stmt = $conn->prepare("INSERT INTO whatsapp_submissions (user_id, promo_id, screenshot_path, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("iis", $user_id, $promo_id, $imageUrl);

if ($stmt->execute()) {
    $_SESSION['whatsapp_success'] = "Submission received! It will be reviewed shortly.";
} else {
    $_SESSION['whatsapp_error'] = "Database error. Please try again.";
}
$stmt->close();

header("Location: ../dashboard/socialmedia.php");
exit();
?>