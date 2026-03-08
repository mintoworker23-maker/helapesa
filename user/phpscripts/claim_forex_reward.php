<?php
session_start();
require_once '../phpscripts/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/forex.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;

// Fetch lesson reward amount
$stmt = $conn->prepare("SELECT reward_amount FROM forex_lessons WHERE id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$result = $stmt->get_result();
$lesson = $result->fetch_assoc();
$stmt->close();

if (!$lesson) {
    header("Location: ../dashboard/forex.php");
    exit();
}

$reward_amount = $lesson['reward_amount'];

// Check if already rewarded
$stmt = $conn->prepare("SELECT id FROM forex_lesson_rewards WHERE user_id = ? AND lesson_id = ?");
$stmt->bind_param("ii", $user_id, $lesson_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: ../dashboard/forex.php");
    exit();
}
$stmt->close();

$conn->begin_transaction();

try {
    // Record reward
    $stmt = $conn->prepare("INSERT INTO forex_lesson_rewards (user_id, lesson_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $lesson_id);
    $stmt->execute();
    $stmt->close();

    // Update user balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("di", $reward_amount, $user_id);
    $stmt->execute();
    $stmt->close();

    // Log transaction
    $desc = "Forex lesson completion reward";
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earn', ?)");
    $stmt->bind_param("ids", $user_id, $reward_amount, $desc);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}

header("Location: ../dashboard/forex.php");
exit();
?>
