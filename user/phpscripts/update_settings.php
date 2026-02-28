<?php
session_start();
include_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

$userId = $_SESSION['user_id'];
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];

$response = ['success' => false, 'message' => ''];

if (empty($email)) {
    $response['message'] = 'Email is required';
    echo json_encode($response);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit();
}

if (!empty($password) && $password !== $confirmPassword) {
    $response['message'] = 'Passwords do not match';
    echo json_encode($response);
    exit();
}

// Load current user
$currentUser = getCurrentUser($conn, $userId);
$profilePicturePath = $currentUser['profile_picture'];

try {
    // Check for duplicate email
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkStmt->bind_param("si", $email, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    if ($result->num_rows > 0) {
        $response['message'] = 'Email already in use';
        echo json_encode($response);
        exit();
    }

    // Update user info
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $hashedPassword, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $email, $userId);
    }

    if ($stmt->execute()) {
        $_SESSION['email'] = $email;
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully!',
            'profile_picture' => '/' . $profilePicturePath // prefix slash for use in HTML src
        ];
    } else {
        $response['message'] = 'Update failed: ' . $conn->error;
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);