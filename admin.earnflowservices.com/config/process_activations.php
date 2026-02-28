<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);

        // Approve Logic
        if (isset($_POST['approve'])) {
            $user_package = $_POST['package'] ?? 'basic'; // Renamed to avoid confusion
            
            // 1. Update user to active and ensure package is set
            $update = $conn->prepare("UPDATE users SET is_active = 1, package = ?, activated_at = NOW() WHERE id = ?");
            $update->bind_param("si", $user_package, $user_id);
            $update->execute();


            $_SESSION['success'] = "User has been successfully activated.";
            header("Location: ../pages/activations.php");
            exit();
        }

        // Reject Logic
        elseif (isset($_POST['reject'])) {
            // Option 1: Delete user (careful!)
            // $delete = $conn->prepare("DELETE FROM users WHERE id = ?");
            // $delete->bind_param("i", $user_id);
            // $delete->execute();

            // Option 2: Mark as rejected (safer)
            $reject = $conn->prepare("UPDATE users SET is_active = -1 WHERE id = ?");
            $reject->bind_param("i", $user_id);
            $reject->execute();

            $_SESSION['error'] = "User activation was rejected.";
            header("Location: ../pages/activations.php");
            exit();
        }
    }
}

// If no valid POST
$_SESSION['error'] = "Invalid request.";
header("Location: ../pages/activations.php");
exit();