<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $amount = floatval($_POST['amount']);

    if (!$user_id || $amount <= 0) {
        $_SESSION['withdraw_error'] = "Invalid withdrawal request.";
        header("Location: ../dashboard/wallet.php");
        exit();
    }

    // Get user's current balance and mpesa_number
    $stmt = $conn->prepare("SELECT balance, phone FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $current_balance = $user['balance'] ?? 0;
    $mpesa_number = $user['phone'] ?? null;

    if ($current_balance < $amount) {
        $_SESSION['withdraw_error'] = "You don’t have enough balance.";
        header("Location: ../dashboard/wallet.php");
        exit();
    }

    if (!$mpesa_number) {
        $_SESSION['withdraw_error'] = "Your M-Pesa number is not set in your profile.";
        header("Location: ../dashboard/wallet.php");
        exit();
    }

    // Insert into withdrawals table (status: pending)
    $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, mpesa_number, amount, status, requested_at, description) VALUES (?, ?, ?, 'pending', NOW(), ?)");
    $description = "Withdrawal to M-Pesa";
    $stmt->bind_param("isds", $user_id, $mpesa_number, $amount, $description);
    $stmt->execute();
    $stmt->close();

    $_SESSION['withdraw_success'] = "Withdrawal request of Ksh " . number_format($amount, 2) . " submitted. It will be processed soon.";
    header("Location: ../dashboard/wallet.php");
    exit();
}