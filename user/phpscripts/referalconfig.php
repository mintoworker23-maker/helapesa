<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('config.php');

$user_id = $_SESSION['user_id'] ?? null;

// Redirect if not logged in
if (!$user_id) {
    header('Location: ../login.php');
    exit;
}

// --- Get referral link ---
$referral_link = "https://earnflowservices.com/user/register.php?ref=";

// Get user's username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user_data = $result->fetch_assoc()) {
    $username = $user_data['username'];
    $referral_link .= $username;
}
$stmt->close();

// --- Get referred users + bonuses ---
$referred_users = [];
$total_bonus = 0;

// Get referral relationships
$stmt = $conn->prepare("
    SELECT r.bonus_amount, u.username, u.email, u.phone, u.created_on
    FROM referals r
    JOIN users u ON r.referred_id = u.id
    WHERE r.referrer_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $referred_users[] = $row;
    $total_bonus += floatval($row['bonus_amount']);
}
$stmt->close();

// --- Get total commission balance ---
$stmt = $conn->prepare("SELECT commission FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$commission_data = $result->fetch_assoc();
$account_balance = $commission_data['commission'] ?? 0;
$stmt->close();

?>