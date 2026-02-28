<?php
session_start();
require_once '../phpscripts/config.php';

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

// Example logic — you can replace this with actual game logic
$multiplierOptions = ['0x', '0.5x', '1x', '2x', '5x', '10x'];
$defaultWeights = [30, 25, 20, 15, 8, 2];

$settingMap = [
  'spin_percent_0x',
  'spin_percent_0_5x',
  'spin_percent_1x',
  'spin_percent_2x',
  'spin_percent_5x',
  'spin_percent_10x'
];

$weights = [];
foreach ($settingMap as $idx => $settingKey) {
  $value = getSiteSetting($conn, $settingKey);
  if ($value === '' || !is_numeric($value)) {
    $weights[] = $defaultWeights[$idx];
    continue;
  }
  $weights[] = max(0, (float)$value);
}

if (array_sum($weights) <= 0) {
  $weights = $defaultWeights;
}

// Weighted random selection
function weighted_random($options, $weights) {
  $total = (float)array_sum($weights);
  $rand = (mt_rand() / mt_getrandmax()) * $total;
  $cumulative = 0;
  foreach ($options as $i => $option) {
    $cumulative += $weights[$i];
    if ($rand <= $cumulative) return $option;
  }
  return end($options);
}

$selectedMultiplier = weighted_random($multiplierOptions, $weights);

echo json_encode(['status' => 'success', 'multiplier' => $selectedMultiplier]);
