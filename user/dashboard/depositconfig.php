<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'config.php';

function normalizeKenyanPhone($rawPhone): ?string {
    $digits = preg_replace('/\D/', '', $rawPhone); // Remove non-digits

    if (preg_match('/^254(7|1)\d{8}$/', $digits)) {
        return '0' . substr($digits, 3);
    } elseif (preg_match('/^(7|1)\d{8}$/', $digits)) {
        return '0' . $digits;
    } elseif (preg_match('/^0(7|1)\d{8}$/', $digits)) {
        return $digits;
    }

    return null; // Invalid
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $amount = floatval($_POST['deposit_amount'] ?? 0);

    if (!$user_id || $amount <= 0) {
        $_SESSION['deposit_error'] = "Invalid deposit request.";
        header("Location: ../dashboard/wallet.php");
        exit();
    }

    // Fetch user phone number
    $stmt = $conn->prepare("SELECT phone FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $phone = $user['phone'] ?? null;
    if (!$phone) {
        $_SESSION['deposit_error'] = "Phone number is missing.";
        header("Location: ../dashboard/wallet.php");
        exit();
    }

    // Normalize and validate phone number
    $phone = normalizeKenyanPhone($phone);
    if (!$phone) {
        $_SESSION['deposit_error'] = "Could not recognize phone number format.";
        header("Location: ../dashboard/wallet.php");
        exit();
    }

    // Lipia API setup
    $lipia_api_key = 'f4211c040b07cb80913191e78c55028e4c573935';
    $lipia_url = 'https://lipia-api.kreativelabske.com/api/request/stk';

    $data = [
        'phone' => $phone,
        'amount' => $amount
    ];

    // Send STK Push
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $lipia_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $lipia_api_key
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        $_SESSION['deposit_error'] = "Payment request error: $err";
    } else {
        $res = json_decode($response, true);
        if (isset($res['data']['reference'])) {
            $reference = $res['data']['reference'];
            $checkoutId = $res['data']['CheckoutRequestID'];

            $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, reference, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("ids", $user_id, $amount, $reference);
            $stmt->execute();
            $stmt->close();

            $_SESSION['deposit_success'] = "STK Push sent. Confirm payment on your phone.";
        } else {
            $errorMsg = $res['message'] ?? 'Unknown error occurred.';
            $_SESSION['deposit_error'] = "Deposit failed: $errorMsg";
        }
    }

    header("Location: ../dashboard/wallet.php");
    exit();
}