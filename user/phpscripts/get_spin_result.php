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
$weights = [30, 25, 20, 15, 8, 2]; // Probabilities (must add up to 100)

// Weighted random selection
function weighted_random($options, $weights) {
  $total = array_sum($weights);
  $rand = mt_rand(1, $total);
  $cumulative = 0;
  foreach ($options as $i => $option) {
    $cumulative += $weights[$i];
    if ($rand <= $cumulative) return $option;
  }
}

$selectedMultiplier = weighted_random($multiplierOptions, $weights);

echo json_encode(['status' => 'success', 'multiplier' => $selectedMultiplier]);