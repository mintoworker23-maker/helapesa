<?php
// Show all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Read from POST (you can test using Postman or a simple form)
$phone = $_POST['phone'] ?? null;
$amount = $_POST['amount'] ?? $_POST['price'] ?? null;

// Fallbacks
$reference = $_POST['reference'] ?? 'Earnflow Payment ' . date('Y-m-d H:i:s');
$description = $_POST['description'] ?? 'Payment';

// Validation
if (!$phone || !$amount) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Phone and amount are required."]);
    exit;
}

// Replace these with your actual Lipia values
$apiKey = "f64c3d018340e12902333a7f087b9fa54550feb4";
$appId = "687e2d7c95590c1538e78f0b";
$baseUrl = "https://lipia-api.kreativelabske.com/api";

// Create request payload
$data = [
    "phone" => $phone,
    "amount" => $amount,
    "reference" => $reference,
    "description" => $description,
    "callback_url" => "https://earnflowservices.com/user/phpscripts/callback.php" // Change this to match your real callback URL
];

// Make cURL request
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "$baseUrl/stk/push",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-API-KEY: $apiKey",
        "X-APP-ID: $appId"
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

// Response handling
if ($curlError) {
    echo json_encode(["success" => false, "error" => "Curl error: $curlError"]);
} elseif (!$response) {
    echo json_encode(["success" => false, "error" => "No response from server."]);
} elseif ($httpCode >= 400) {
    echo json_encode(["success" => false, "error" => "Server returned HTTP $httpCode", "response" => $response]);
} else {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode(["success" => true, "data" => $decoded]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid JSON response", "raw" => $response]);
    }
}
?>