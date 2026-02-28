<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log'); // logs to a file
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Collect form inputs
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $country = $_POST['country'];
    $referral_code = trim($_POST['referral_code'] ?? '');

    // Basic validation
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Passwords do not match!";
        header("Location: ../register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Invalid email address.";
        header("Location: ../register.php");
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = "Email already exists.";
        header("Location: ../register.php");
        exit;
    }
    $stmt->close();

    // Check username or phone
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone = ?");
    $stmt->bind_param("ss", $username, $phone);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = "Username or phone already in use.";
        header("Location: ../register.php");
        exit;
    }
    $stmt->close();

    // Initialize referral fields
    $referred_by = null;
    $grand_referrer = null;

    if (!empty($referral_code)) {
        $stmt = $conn->prepare("SELECT id, referred_by FROM users WHERE username = ?");
        $stmt->bind_param("s", $referral_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $referrer_data = $result->fetch_assoc();
            $referred_by = $referrer_data['id'];
            $grand_referrer = $referrer_data['referred_by'] ?? null;
        } else {
            $_SESSION['register_error'] = "Invalid referral code.";
            header("Location: ../register.php");
            exit;
        }
        $stmt->close();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, phone, email, password, country, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $username, $phone, $email, $hashed_password, $country, $referred_by);
    
    if ($stmt->execute()) {
        $new_user_id = $conn->insert_id;
        $stmt->close();

            // ✅ Credit welcome bonus from admin settings (0 disables it)
            $welcome_bonus_setting = getSiteSetting($conn, 'welcome_bonus');
            $welcome_bonus = is_numeric($welcome_bonus_setting) ? max(0, (float)$welcome_bonus_setting) : 150.00;

            if ($welcome_bonus > 0) {
                // 1. Update user balance
                $stmt_bonus = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt_bonus->bind_param("di", $welcome_bonus, $new_user_id);
                $stmt_bonus->execute();
                $stmt_bonus->close();

                // 2. Log in transactions
                $desc = "Welcome bonus";
                $stmt_log = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earn', ?)");
                $stmt_log->bind_param("ids", $new_user_id, $welcome_bonus, $desc);
                $stmt_log->execute();
                $stmt_log->close();

                $bonus_display = (fmod($welcome_bonus, 1.0) == 0.0) ? number_format($welcome_bonus, 0) : number_format($welcome_bonus, 2);
                $_SESSION['welcome_bonus'] = "🎉 You’ve received Ksh {$bonus_display} welcome bonus!";
            }



        // Insert level 1 referral
        if ($referred_by) {
            $stmt1 = $conn->prepare("INSERT INTO referals (referrer_id, referred_id, level) VALUES (?, ?, 1)");
            $stmt1->bind_param("ii", $referred_by, $new_user_id);
            $stmt1->execute();
            $stmt1->close();

            // Insert level 2 referral (if applicable)
            if (!empty($grand_referrer)) {
                $stmt2 = $conn->prepare("INSERT INTO referals (referrer_id, referred_id, level) VALUES (?, ?, 2)");
                $stmt2->bind_param("ii", $grand_referrer, $new_user_id);
                $stmt2->execute();
                $stmt2->close();
            }
        }

        $_SESSION['register_success'] = "Registration successful. Please activate your account.";
        $_SESSION['phone'] = $phone;
        header("Location: ../activation.php");
        exit;
    } else {
        $_SESSION['register_error'] = "Registration failed. Please try again.";
        header("Location: ../register.php");
        exit;
    }
}
?>
