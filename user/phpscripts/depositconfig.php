<?php
// Start session and set headers
session_start();
header('Content-Type: application/json');

// Load DB connection if needed
include_once '../config.php'; // Adjust path as needed

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Validate POST input
$amount = isset($_POST['deposit_amount']) ? floatval($_POST['deposit_amount']) : 0;
if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid deposit amount']);
    exit;
}

// Get user's phone number from DB or session (example here assumes session)
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : null;
if (!$phone || !preg_match('/^(07|01)\d{8}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing phone number']);
    exit;
}

// Prepare API request to Lipia
$api_key = "066c867696d4fe3c43b36fc04cf4af023726cbc2"; // Replace with your actual Lipia key
$api_url = "https://lipia-api.kreativelabske.com/api/request/stk";

// Format phone to international
if (str_starts_with($phone, '07')) {
    $phone = '254' . substr($phone, 1);
} elseif (str_starts_with($phone, '01')) {
    $phone = '254' . substr($phone, 1);
}

// cURL request
$payload = json_encode([
    "phone" => $phone,
    "amount" => $amount
]);

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle response
if ($httpcode === 200 && $response) {
    $result = json_decode($response, true);
    if (isset($result['data']['CheckoutRequestID'])) {
        echo json_encode([
            'success' => true,
            'message' => 'STK push sent to phone',
            'data' => $result['data']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Unknown error from Lipia',
            'debug' => $result
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Request failed. HTTP Code: ' . $httpcode,
        'error' => $error,
        'response' => $response
    ]);
}