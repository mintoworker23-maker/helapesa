<?php
session_start();
require_once '../phpscripts/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['video_error'] = "You must be logged in to submit a reward.";
    header("Location: ../dashboard/youtube.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$video_id = isset($_POST['video_id']) ? (int)$_POST['video_id'] : 0;
$reward_amount = 100;

// Check if already viewed
$stmt = $conn->prepare("SELECT id FROM video_views WHERE user_id = ? AND video_id = ? AND DATE(viewed_at) = CURDATE()");
$stmt->bind_param("ii", $user_id, $video_id); // Bind both user_id and video_id here
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['video_error'] = "You’ve already been rewarded for this video.";
    header("Location: ../dashboard/youtube.php");
    exit();
}

$stmt->close();

$conn->begin_transaction();

try {
    // Record the view
    $stmt = $conn->prepare("INSERT INTO video_views (user_id, video_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $video_id);
    $stmt->execute();
    $stmt->close();

    // Update user balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("ii", $reward_amount, $user_id);
    $stmt->execute();
    $stmt->close();

    // Log the transaction
    $desc = "Video watch reward";
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earn', ?)");
    $stmt->bind_param("iis", $user_id, $reward_amount, $desc);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    $_SESSION['video_success'] = "✅ You've been rewarded Ksh 100 for watching!";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['video_error'] = "Something went wrong. Try again.";
}

header("Location: ../dashboard/youtube.php");
exit();
?>
