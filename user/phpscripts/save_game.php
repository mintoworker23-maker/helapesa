<?php
session_start();
require_once '../phpscripts/config.php';

// Validate session
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$bet = intval($data['bet']);
$multiplier = floatval($data['multiplier']);
$win = intval($data['win']);

// Fetch current balance and free spins
$stmt = $conn->prepare("SELECT balance, free_spins FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_balance, $free_spins);
if (!$stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}
$stmt->close();

$is_free_spin = ($bet == 0 && $free_spins > 0);

// Check for enough balance or free spin
if (!$is_free_spin && $current_balance < $bet) {
    echo json_encode(['status' => 'error', 'message' => 'Insufficient balance']);
    exit;
}

// Update user balance and free spins
if ($is_free_spin) {
    $new_balance = $current_balance + $win;
    $stmt = $conn->prepare("UPDATE users SET balance = ?, free_spins = free_spins - 1 WHERE id = ?");
    $stmt->bind_param("di", $new_balance, $user_id);
} else {
    $new_balance = $current_balance - $bet + $win;
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param("di", $new_balance, $user_id);
}
$stmt->execute();
$stmt->close();

// Save game history
$stmt = $conn->prepare("INSERT INTO game_history (user_id, bet, multiplier, win) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iidi", $user_id, $bet, $multiplier, $win);
$stmt->execute();
$stmt->close();

// log into transactions table only if there is a profit
$profit = $win - $bet;
if ($profit > 0) {
    $description = "Spin & Win profit";
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earn', ?)");
    $stmt->bind_param("ids", $user_id, $profit, $description);
    $stmt->execute();
    $stmt->close();
}


// Return success
echo json_encode(['status' => 'success', 'new_balance' => $new_balance]);