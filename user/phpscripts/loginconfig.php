<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';

// Sanitize input
$identifier = trim($_POST['identifier']);
$password = $_POST['password'];

// Check if user exists
$sql = "SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $identifier, $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_active'] = $user['is_active'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['package'] = $user['package']; // ✅ This was missing
        $_SESSION['login_success'] = "Login successful! Welcome, " . $user['username'] . ".";

        // Handle activation status
        if ($user['is_active'] == 1) {
            $_SESSION['activated'] = true;
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            $_SESSION['activated'] = false;
            header("Location: ../activation.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Incorrect password.";
        header("Location: ../login.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = "Account not found. Check your username, phone, or email.";
    header("Location: ../login.php");
    exit();
}
?>