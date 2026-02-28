<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

if (isset($_POST['Login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if ($password === $user['password']) {
            $_SESSION['email'] = $user['email'];
            // Redirect to dashboard or wherever
            header("Location: ../pages/index.php");
            exit();
        }
    }

    // Login failed
    $_SESSION['login_error'] = 'Incorrect email or password';
    header("Location: ../index.php");
    exit();
}
?>