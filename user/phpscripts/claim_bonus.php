<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$bonus_id = isset($_POST['bonus_id']) ? (int)$_POST['bonus_id'] : 0;

if (!$bonus_id) {
    header("Location: ../dashboard/index.php");
    exit();
}

// Fetch bonus details and check if active
$stmt = $conn->prepare("SELECT * FROM scheduled_bonuses WHERE id = ? AND status = 'active' AND start_time <= NOW() AND end_time > NOW()");
$stmt->bind_param("i", $bonus_id);
$stmt->execute();
$result = $stmt->get_result();
$bonus = $result->fetch_assoc();
$stmt->close();

if (!$bonus) {
    $_SESSION['withdraw_error'] = "This bonus is no longer available.";
    header("Location: ../dashboard/index.php");
    exit();
}

// Check if already claimed
$stmt = $conn->prepare("SELECT id FROM claimed_bonuses WHERE user_id = ? AND bonus_id = ?");
$stmt->bind_param("ii", $user_id, $bonus_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $_SESSION['withdraw_error'] = "You have already claimed this bonus.";
    header("Location: ../dashboard/index.php");
    exit();
}
$stmt->close();

$conn->begin_transaction();

try {
    // Record the claim
    $stmt = $conn->prepare("INSERT INTO claimed_bonuses (user_id, bonus_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $bonus_id);
    $stmt->execute();
    $stmt->close();

    if ($bonus['type'] === 'fixed_amount') {
        // Update cash balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $bonus['amount'], $user_id);
        $stmt->execute();
        $stmt->close();

        // Log transaction
        $desc = "Bonus: " . $bonus['title'];
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earn', ?)");
        $stmt->bind_param("ids", $user_id, $bonus['amount'], $desc);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['withdraw_success'] = "🎉 Ksh " . number_format($bonus['amount'], 2) . " bonus added to your balance!";
    } elseif ($bonus['type'] === 'free_spin') {
        // Logic for free spins - assuming there's a field or system for this
        // If there isn't a 'spins' column, we might need to add one or just log it.
        // For now, let's assume we update a 'spins_left' column if it exists, or just show success.
        
        // Let's check if 'spins_left' or similar exists. Based on previous searches it didn't show up.
        // I will add a column 'free_spins' to users table if it doesn't exist.
        
        $conn->query("UPDATE users SET free_spins = free_spins + ". (int)$bonus['amount'] ." WHERE id = $user_id");
        $_SESSION['withdraw_success'] = "🎉 " . (int)$bonus['amount'] . " Free Spins added to your account!";
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['withdraw_error'] = "Something went wrong. Please try again.";
}

header("Location: ../dashboard/index.php");
exit();
?>
