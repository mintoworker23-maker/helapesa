<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();


require_once 'config.php'; 



// === Swift-Wallet Settings ===
$swiftApiKey  = 'a1fab1c0fa6fee5f5b280747fac723bd11bb0d2ec7de88712b9837ed73667953';
$swiftUrl     = 'https://swiftwallet.co.ke/pay-app-v2/payments.php';
$callbackUrl  = 'https://earnflowservices.com/user/phpscripts/callback.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Invalid request method']);
    exit;
}

// --- Gather & sanitize inputs ---
$userId        = $_SESSION['user_id'];
$rawPhone      = $_POST['phone']        ?? '';
$referredBy    = intval($_POST['referred_by'] ?? 0); // <— pull in the referring user’s ID
$paymentMethod = $_POST['pay_method']   ?? 'automatic';
$price         = isset($_POST['Price'])
                   ? floatval(preg_replace('/[^\d.]/','',$_POST['Price']))
                   : 1300.0;

// --- Normalize to E.164 (2547XXXXXXXX) ---
$phone = trim($rawPhone);
if (preg_match('/^0\d{9}$/', $phone)) {
    $phone = '254'.substr($phone,1);
} elseif (preg_match('/^7\d{8}$/', $phone)) {
    $phone = '254'.$phone;
}
if (! preg_match('/^2547\d{8}$/', $phone)) {
    echo json_encode(['status'=>'error','message'=>'Invalid phone format']);
    exit;
}

// --- Check for existing user & activation status ---
$stmt = $conn->prepare("SELECT id, is_active FROM users WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Already activated?
if ($user && $user['is_active'] == 1) {
    echo json_encode(['status'=>'error','message'=>'User already activated']);
    exit;
}

// --- Manual activation flow ---
if ($paymentMethod === 'manual') {
    if ($user) {
        $package = $_POST['package'] ?? 'basic'; // Get package from POST
        $upd = $conn->prepare(
          "UPDATE users
              SET referred_by = ?, is_active = 0, package = ?
            WHERE phone = ?"
        );
        $upd->bind_param("iss", $referredBy, $package, $phone);
        $upd->execute();
    }

    // Optionally log the referral
    if ($referredBy) {
        $r = $conn->prepare(
          "INSERT INTO referals (referrer_id, referred_id, bonus_amount, level, paid)
           VALUES (?, ?, 0, 1, 0)"
        );
        $r->bind_param("ii", $referredBy, $user['id']);
        $r->execute();
    }

    echo json_encode([
      'status'  => 'success',
      'message' => 'Your Transactional Code has been recieved successfully'
    ]);
    exit;
}

// --- Automatic STK Push via Swift-Wallet ---
$externalRef = uniqid('ACT_');

$payload = [
    'amount'             => (int)$price,
    'phone_number'       => $phone,
  // 'channel_id'         => $swiftChannelID, // omit if you want default
    'external_reference' => $externalRef,
    'callback_url'       => $callbackUrl
];

$ch = curl_init($swiftUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer {$swiftApiKey}",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$responseJson = curl_exec($ch);
$curlErr      = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['status'=>'error','message'=>"Network error: {$curlErr}"]);
    exit;
}

$resp = json_decode($responseJson, true);
error_log("SwiftWallet /payments response: ".print_r($resp, true));

if (!empty($resp['success']) && strtoupper($resp['status']) === 'INITIATED') {
    // record pending activation transaction
    $t = $conn->prepare(
      "INSERT INTO transactions
         (reference, user_id, amount, type, status, created_at)
       VALUES (?,?,?,?, 'pending', NOW())"
    );
    $type = 'activation';
    $t->bind_param("sdis", $externalRef, $userId, $price, $type);
    $t->execute();

    echo json_encode([
      'status'    => 'success',
      'message'   => $resp['message'] ?? 'STK Push sent. Check your phone.',
      'reference' => $externalRef
    ]);
} else {
    $errMsg = $resp['message'] 
            ?? $resp['error'] 
            ?? 'Payment initiation failed.';
    echo json_encode(['status'=>'error','message'=>$errMsg]);
}