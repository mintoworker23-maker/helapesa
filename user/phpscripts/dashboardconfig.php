<?php
require_once '../phpscripts/config.php';

$user_id = $_SESSION['user_id'] ?? 0;

$dashboard_data = [
  'current_balance' => 0.00,
  'total_earned' => 0.00,
  'total_withdrawn' => 0.00,
  'referral_count' => 0,
  'referral_bonus' => 0.00,
  'earning_trend' => [],
  'referral_trend' => [],
  'leaderboard' => []
];

if ($user_id) {
  $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $balance = 0;
  if ($result && $result->num_rows) {
    $balance = (float)$result->fetch_assoc()['balance'];
    $dashboard_data['current_balance'] = number_format($balance, 2);
  }
  $stmt->close();

  $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id = ? AND type = 'withdraw'");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $withdrawn = 0;
  if ($result) {
    $withdrawn = (float)($result->fetch_assoc()['total'] ?? 0);
    $dashboard_data['total_withdrawn'] = number_format($withdrawn, 2);
  }
  $stmt->close();

  $dashboard_data['total_earned'] = number_format($balance + $withdrawn, 2);

  $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM referals WHERE referrer_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result) {
    $dashboard_data['referral_count'] = (int)($result->fetch_assoc()['count'] ?? 0);
  }
  $stmt->close();

  $stmt = $conn->prepare("SELECT SUM(bonus_amount) AS total FROM referals WHERE referrer_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result) {
    $dashboard_data['referral_bonus'] = number_format((float)($result->fetch_assoc()['total'] ?? 0), 2);
  }
  $stmt->close();

  $stmt = $conn->prepare("SELECT DATE(created_at) AS date, SUM(amount) AS total FROM transactions WHERE user_id = ? GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC LIMIT 7");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $dashboard_data['earning_trend'][] = [
      'date' => $row['date'],
      'total' => (float)$row['total']
    ];
  }
  $stmt->close();

  $stmt = $conn->prepare("SELECT DATE(created_at) AS date, COUNT(*) AS count FROM referals WHERE referrer_id = ? GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC LIMIT 7");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $dashboard_data['referral_trend'][] = [
      'date' => $row['date'],
      'count' => (int)$row['count']
    ];
  }
  $stmt->close();

  $stmt = $conn->prepare("SELECT username, balance FROM users ORDER BY balance DESC LIMIT 5");
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $dashboard_data['leaderboard'][] = [
      'username' => $row['username'],
      'total' => (float)$row['balance']
    ];
  }
  $stmt->close();
}?>