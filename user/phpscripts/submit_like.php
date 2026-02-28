<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$ad_id = isset($_POST['ad_id']) ? (int)$_POST['ad_id'] : 0;
$reward_amount = 50;

// === 1. Get user package
$stmt = $conn->prepare("SELECT package FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$package = strtolower($user['package'] ?? '');

$limits = [
    'silver' => ['period' => 'WEEK', 'limit' => 3],
    'gold'   => ['period' => 'WEEK', 'limit' => 6],
    'premium'=> ['period' => 'DAY', 'limit' => 5],
];

// Default to 0 limit if unknown package
$limitInfo = $limits[$package] ?? ['period' => 'DAY', 'limit' => 0];

// === 2. Check ad like count within period
if ($limitInfo['period'] === 'WEEK') {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM ad_likes WHERE user_id = ? AND YEARWEEK(liked_at, 1) = YEARWEEK(CURDATE(), 1)");
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM ad_likes WHERE user_id = ? AND DATE(liked_at) = CURDATE()");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$likes_in_period = (int)($row['count'] ?? 0);
$stmt->close();

if ($likes_in_period >= $limitInfo['limit']) {
    $msg = "Limit reached: {$limitInfo['limit']} ad(s) per {$limitInfo['period']} for your package.";
    echo json_encode(['success' => false, 'message' => $msg]);
    exit();
}

// === 3. Prevent duplicate ad like
$stmt = $conn->prepare("SELECT id FROM ad_likes WHERE user_id = ? AND ad_id = ?");
$stmt->bind_param("ii", $user_id, $ad_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You already liked this ad.']);
    exit();
}
$stmt->close();

// === 4. Confirm ad exists and is active
$stmt = $conn->prepare("SELECT id FROM like_ads WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $ad_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Ad not found or inactive.']);
    exit();
}
$stmt->close();

// === 5. Reward the user
$conn->begin_transaction();
try {
    // Insert like record
    $stmt = $conn->prepare("INSERT INTO ad_likes (user_id, ad_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $ad_id);
    $stmt->execute();
    $stmt->close();

    // Add reward
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("ii", $reward_amount, $user_id);
    $stmt->execute();
    $stmt->close();

    // Log transaction
    $desc = "Liked ad ID: $ad_id";
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'earn', ?)");
    $stmt->bind_param("iis", $user_id, $reward_amount, $desc);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ad liked. Ksh 50 awarded!']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Try again.']);
}
?>