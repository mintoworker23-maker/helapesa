<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$raw = file_get_contents('php://input');
file_put_contents('callback_log.txt', "[".date('Y-m-d H:i:s')."] ".$raw.PHP_EOL, FILE_APPEND);

$data = json_decode($raw, true);
if (! $data) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Swift-Wallet callback structure sends:
//
// {
//   "success": true,
//   "transaction_id": 12847,
//   "external_reference": "ACT_…",
//   "checkout_request_id": "…",
//   "merchant_request_id": "…",
//   "status": "completed",    // or "failed"
//   "timestamp": "…",
//   "service_fee": 15.00,
//   "result": {
//     "ResultCode": 0,
//     "ResultDesc": "...",
//     "Amount": 1300,
//     "MpesaReceiptNumber": "ABC123",
//     "Phone": "2547XXXXXXXX"
//   },
//   "channel_info": { … }
// }

if (empty($data['external_reference'])) {
    http_response_code(400);
    exit('Missing external_reference');
}

// Always acknowledge to avoid retries
http_response_code(200);
echo json_encode(['ResultCode'=>0,'ResultDesc'=>'Success']);

// Only proceed if status is “completed”
if (strtolower($data['status'] ?? '') !== 'completed') {
    // you could log failures or ignore
    exit;
}

// Extract phone and amount from result
$phone  = $data['result']['Phone']  ?? null;
$amount = $data['result']['Amount'] ?? null;
$ref    = $data['external_reference'];

if (!$phone) {
    error_log("Callback missing phone in result");
    exit;
}

// 1) Mark transaction completed
$u1 = $conn->prepare("
  UPDATE transactions
     SET status      = 'completed',
         mpesa_receipt = ?,
         service_fee   = ?,
         updated_at   = NOW()
   WHERE reference = ?
");
$receipt   = $data['result']['MpesaReceiptNumber'] ?? null;
$serviceFee= $data['service_fee'] ?? 0.0;
$u1->bind_param("sds", $receipt, $serviceFee, $ref);
$u1->execute();

// 2) Activate the user
// Try to determine package from amount, prioritizing packages table
$package_field = "";
$check_pkg = $conn->query("SHOW TABLES LIKE 'packages'");
if ($check_pkg && $check_pkg->num_rows > 0) {
    // Find package name by price
    $stmt_pkg = $conn->prepare("SELECT name FROM packages WHERE price = ? LIMIT 1");
    // Ensure accurate float matching or use a range if needed, here exact match assumes admin set exact prices
    $stmt_pkg->bind_param("d", $amount);
    if ($stmt_pkg->execute()) {
        $res_pkg = $stmt_pkg->get_result();
        if ($pkg_row = $res_pkg->fetch_assoc()) {
            $package_name = strtolower($pkg_row['name']);
            // Check if 'package' column exists in users table to update it
            // (Assuming it exists based on login scripts)
            $package_field = ", package = '{$package_name}'";
        }
    }
}

$u2 = $conn->prepare("
  UPDATE users
     SET is_active = 1{$package_field}
   WHERE phone = ?
");
$u2->bind_param("s", $phone);
$u2->execute();

// 3) (Optional) You could also credit their balance or record this activation 
//    in another audit table if you need to track earned balances, etc.

// Done.