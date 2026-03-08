<?php
session_start();
require_once '../phpscripts/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/ebooks.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$ebook_id = isset($_POST['ebook_id']) ? (int)$_POST['ebook_id'] : 0;

// Fetch reward amount and file path
$stmt = $conn->prepare("SELECT reward_amount, file_path FROM business_ebooks WHERE id = ?");
$stmt->bind_param("i", $ebook_id);
$stmt->execute();
$result = $stmt->get_result();
$ebook = $result->fetch_assoc();
$stmt->close();

if (!$ebook) {
    header("Location: ../dashboard/ebooks.php");
    exit();
}

$reward_amount = $ebook['reward_amount'];

// Check if already rewarded
$stmt = $conn->prepare("SELECT id FROM ebook_rewards WHERE user_id = ? AND ebook_id = ?");
$stmt->bind_param("ii", $user_id, $ebook_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Already rewarded, but still allow download
    $stmt->close();
    header("Location: ../../" . $ebook['file_path']);
    exit();
}
$stmt->close();

$conn->begin_transaction();

try {
    // Record reward
    $stmt = $conn->prepare("INSERT INTO ebook_rewards (user_id, ebook_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $ebook_id);
    $stmt->execute();
    $stmt->close();

    // Update user balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("di", $reward_amount, $user_id);
    $stmt->execute();
    $stmt->close();

    // Log transaction
    $desc = "Ebook download reward";
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earn', ?)");
    $stmt->bind_param("ids", $user_id, $reward_amount, $desc);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}

// Redirect to the file for download
header("Location: ../../" . $ebook['file_path']);
exit();
?>
