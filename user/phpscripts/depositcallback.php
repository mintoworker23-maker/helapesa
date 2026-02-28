<?php
// Start session and include DB config
session_start();
include_once 'config.php'; // Your DB connection file

// Set header to JSON
header('Content-Type: application/json');

// Read raw POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Validate payload
if ($data['data']['status'] !== 'success') {
    // Don't record failed or cancelled payments
    echo json_encode(['success' => false, 'message' => 'Payment not successful']);
    exit;
}

$phone = $data['data']['phone'];
$amount = $data['data']['amount'];
$reference = $data['data']['reference'];
$checkoutRequestId = $data['data']['CheckoutRequestID'];

try {
    // Get user by phone (Assuming there's a users table with 'phone' field)
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $user_id = $user['id'];

    // Get user's last balance
    $stmt = $conn->prepare("SELECT balance_after FROM transaction WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastTransaction = $result->fetch_assoc();
    $previous_balance = $lastTransaction ? $lastTransaction['balance_after'] : 0.00;

    // Calculate new balance
    $new_balance = $previous_balance + $amount;

    // Insert into transaction table
    $insert = $conn->prepare("INSERT INTO transaction (user_id, type, source, amount, description, status, balance_after, created_at) 
                              VALUES (?, 'earn', 'deposit', ?, ?, 'completed', ?, NOW())");
    $desc = "Deposit via MPESA Ref: $reference";
    $insert->bind_param("idssd", $user_id, $amount, $desc, $new_balance);
    $insert->execute();

    echo json_encode(['success' => true, 'message' => 'Transaction recorded successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>