<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = intval($_POST['submission_id']);
    $action = $_POST['action'];

    if (!$submission_id || !in_array($action, ['approve', 'reject'])) {
        $_SESSION['error'] = "Invalid request.";
        header("Location: ../pages/socialmediaads.php");
        exit;
    }

    // Fetch submission with user
    $stmt = $conn->prepare("SELECT ws.*, u.balance FROM whatsapp_submissions ws JOIN users u ON ws.user_id = u.id WHERE ws.id = ?");
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();
    $stmt->close();

    if (!$submission) {
        $_SESSION['error'] = "Submission not found.";
        header("Location: ../pages/socialmediaads.php");
        exit;
    }

    // If already approved, prevent further reward
    if ($submission['status'] === 'approved') {
        $_SESSION['error'] = "This submission has already been approved and rewarded.";
        header("Location: ../pages/socialmediaads.php?promo_id={$submission['promo_id']}");
        exit;
    }

    // Handle rejection
    if ($action === 'reject') {
        $update = $conn->prepare("UPDATE whatsapp_submissions SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $submission_id);
        $update->execute();
        $update->close();

        $_SESSION['success'] = "Submission rejected.";
        header("Location: ../pages/socialmediaads.php?promo_id={$submission['promo_id']}");
        exit;
    }

    // Approve: Check views
    $views = intval($_POST['views'] ?? 0);
    if ($views < 1) {
        $_SESSION['error'] = "Enter a valid view count.";
        header("Location: ../pages/socialmediaads.php?promo_id={$submission['promo_id']}");
        exit;
    }

    // Calculate reward
    $reward = $views * 150;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update submission
        $stmt1 = $conn->prepare("UPDATE whatsapp_submissions SET status = 'approved', views = ? WHERE id = ?");
        $stmt1->bind_param("ii", $views, $submission_id);
        $stmt1->execute();

        // Update user balance
        $stmt2 = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt2->bind_param("ii", $reward, $submission['user_id']);
        $stmt2->execute();

        // Log transaction
        $desc = "Reward for WhatsApp promo (#{$submission['promo_id']})";
        $type = "credit";
        $stmt3 = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt3->bind_param("iiss", $submission['user_id'], $reward, $type, $desc);
        $stmt3->execute();

        $conn->commit();

        $_SESSION['success'] = "Submission approved and user rewarded.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Something went wrong: " . $e->getMessage();
    }

    header("Location: ../pages/socialmediaads.php?promo_id={$submission['promo_id']}");
    exit;
}