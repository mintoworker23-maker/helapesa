<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php';

// Simulate a user login (for demo/testing purposes)
if (!isset($_SESSION['user_id'])) {
  $_SESSION['user_id'] = 1; // Replace with actual login logic
}

// Check package permission for Spin & Win
$package = strtolower(trim($_SESSION['package'] ?? 'basic'));
$canAccessSpinWin = 1; // Keep legacy behavior enabled by default
$stmt = $conn->prepare("SELECT * FROM packages WHERE LOWER(name) = ? LIMIT 1");
if ($stmt) {
  $stmt->bind_param("s", $package);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $canAccessSpinWin = isset($row['access_spin_win']) ? (int)$row['access_spin_win'] : 1;
  }
  $stmt->close();
}

if (!$canAccessSpinWin) {
  $_SESSION['spin_error'] = "Your package does not include Spin & Win access.";
  header("Location: index.php");
  exit();
}

// Fetch user balance from the database
$user_id = $_SESSION['user_id'];
$balance = 0;

$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();

// Fetch recent play history
$history = [];
$stmt = $conn->prepare("SELECT bet, multiplier, win FROM game_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($bet, $multiplier, $win);
while ($stmt->fetch()) {
  $history[] = [
    'bet' => $bet,
    'multiplier' => $multiplier,
    'win' => $win
  ];
}
$stmt->close();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 text-center mb-4">
            <h2 class="font-weight-bolder">Fortune Spin Wheel</h2>
            <p class="text-muted">Test your luck and win big with our fortune wheel!</p>
        </div>
    </div>

    <div class="row">
        <!-- LEFT: SPIN WHEEL -->
        <div class="col-lg-7 mb-4">
            <div class="card feature-card h-100 d-flex justify-content-center align-items-center p-4">
                <div style="position: relative; width: 100%; max-width: 400px; aspect-ratio: 1/1;">
                    <!-- Pointer Triangle -->
                    <div style="
                      position: absolute;
                      top: -15px;
                      left: 50%;
                      transform: translateX(-50%);
                      width: 0;
                      height: 0;
                      border-left: 20px solid transparent;
                      border-right: 20px solid transparent;
                      border-bottom: 30px solid #f44336;
                      z-index: 10;
                      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
                    "></div>

                    <!-- Actual Canvas -->
                    <canvas id="wheelCanvas" width="400" height="400" style="width: 100%; height: auto;"></canvas>
                </div>
            </div>
        </div>

        <!-- RIGHT: BET & HISTORY -->
        <div class="col-lg-5">
            <div class="card feature-card mb-4">
                <div class="card-body text-center p-4">
                    <h5 class="text-muted text-uppercase small font-weight-bold mb-2">Available Balance</h5>
                    <h3 class="font-weight-bolder mb-4">Ksh <span id="currentBalance"><?= number_format($balance, 2) ?></span></h3>
                    
                    <div class="form-group mb-3 text-start">
                        <label for="betAmount" class="form-label font-weight-bold">Enter Your Bet (Ksh)</label>
                        <input type="number" id="betAmount" placeholder="e.g. 20" min="1" class="form-control" />
                    </div>
                    
                    <button id="spinBtn" class="btn btn-dark btn-custom w-100 mt-2">
                        <span class="material-symbols-rounded align-middle me-1">autorenew</span> SPIN NOW
                    </button>
                </div>
            </div>

            <div class="card feature-card">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold">Recent Plays</h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Bet</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Mult.</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-end">Win</th>
                                </tr>
                            </thead>
                            <tbody id="historyBody">
                                <?php if (!empty($history)): ?>
                                    <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">Ksh <?= number_format($h['bet'], 2) ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-sm bg-gradient-info"><?= $h['multiplier'] ?>x</span>
                                        </td>
                                        <td class="align-middle text-end">
                                            <span class="text-<?= $h['win'] > 0 ? 'success' : 'danger' ?> text-sm font-weight-bold">
                                                Ksh <?= number_format($h['win'], 2) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted text-xs">No plays yet. Spin to start!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<audio id="winSound" src="assets/sounds/win.mp3" preload="auto"></audio>
<audio id="loseSound" src="assets/sounds/lose.mp3" preload="auto"></audio>

<!-- Winwheel + GSAP -->
<script src="https://cdn.jsdelivr.net/gh/zarocknz/javascript-winwheel/Winwheel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<script>
const segments = [
  { text: '0x', fillStyle: '#f44336', textFillStyle: '#ffffff' },
  { text: '0.5x', fillStyle: '#9c27b0', textFillStyle: '#ffffff' },
  { text: '1x', fillStyle: '#3f51b5', textFillStyle: '#ffffff' },
  { text: '2x', fillStyle: '#2196f3', textFillStyle: '#ffffff' },
  { text: '5x', fillStyle: '#4caf50', textFillStyle: '#ffffff' },
  { text: '10x', fillStyle: '#ff9800', textFillStyle: '#ffffff' }
];

let theWheel = new Winwheel({
  'canvasId': 'wheelCanvas',
  'numSegments': segments.length,
  'outerRadius': 180,
  'textFontSize': 24,
  'responsive': true,
  'segments': segments,
  'animation': {
    'type': 'spinToStop',
    'duration': 5,
    'spins': 8,
    'callbackFinished': showResult
  }
});

let betAmount = 0;

document.getElementById('spinBtn').addEventListener('click', function () {
  betAmount = parseFloat(document.getElementById('betAmount').value);
  let currentBalanceStr = document.getElementById('currentBalance').innerText.replace(/,/g, '');
  let currentBalance = parseFloat(currentBalanceStr);

  if (!betAmount || betAmount <= 0) {
    alertify.error("Please enter a valid bet.");
    return;
  }

  if (betAmount > currentBalance) {
    alertify.error("You don’t have enough balance to place this bet.");
    return;
  }

  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> SPINNING...';

  fetch('../phpscripts/get_spin_result.php')
    .then(res => res.json())
    .then(data => {
      if (data.status !== 'success') {
        alertify.error(data.message || "Could not get spin result.");
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-rounded align-middle me-1">autorenew</span> SPIN NOW';
        return;
      }

      const multiplierText = data.multiplier;
      const targetSegmentIndex = segments.findIndex(seg => seg.text === multiplierText);

      if (targetSegmentIndex === -1) {
        alertify.error("Invalid multiplier from server.");
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-rounded align-middle me-1">autorenew</span> SPIN NOW';
        return;
      }

      // Reset wheel completely
      theWheel.stopAnimation(false);
      theWheel.rotationAngle = 0;
      theWheel.draw();
      theWheel._selectedMultiplier = multiplierText;

      // Use segment's stopAngle for accurate spin
      theWheel.animation.stopAngle = theWheel.getRandomForSegment(targetSegmentIndex + 1);
      theWheel.startAnimation();
    })
    .catch(err => {
      alertify.error("Spin failed. Check connection.");
      console.error(err);
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-rounded align-middle me-1">autorenew</span> SPIN NOW';
    });
});

function showResult(indicatedSegment) {
  const multiplier = parseFloat(theWheel._selectedMultiplier.replace('x', ''));
  const winAmount = Math.round(betAmount * multiplier);

  if (multiplier === 0) {
    alertify.error(`💀 You lost your Ksh ${betAmount}`);
    try { document.getElementById('loseSound').play(); } catch(e) {}
  } else {
    alertify.success(`🎉 You won Ksh ${winAmount} (${indicatedSegment.text})`);
    try { document.getElementById('winSound').play(); } catch(e) {}
  }

  fetch('../phpscripts/save_game.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      bet: betAmount,
      multiplier: multiplier,
      win: winAmount
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      document.getElementById('currentBalance').innerText = parseFloat(data.new_balance).toLocaleString(undefined, {minimumFractionDigits: 2});
      updateHistory(betAmount, multiplier, winAmount);
    } else {
      alertify.error(data.message);
    }

    // Enable the button again
    const btn = document.getElementById('spinBtn');
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-rounded align-middle me-1">autorenew</span> SPIN NOW';
  });
}

function updateHistory(bet, multiplier, win) {
  const tbody = document.getElementById('historyBody');
  // Remove "No plays yet" row if it exists
  if (tbody.rows.length === 1 && tbody.rows[0].cells.length === 1) {
    tbody.innerHTML = '';
  }
  
  const row = tbody.insertRow(0);
  const winClass = win > 0 ? 'success' : 'danger';
  row.innerHTML = `
    <td><p class="text-sm font-weight-bold mb-0">Ksh ${parseFloat(bet).toFixed(2)}</p></td>
    <td class="align-middle text-center"><span class="badge badge-sm bg-gradient-info">${multiplier}x</span></td>
    <td class="align-middle text-end"><span class="text-${winClass} text-sm font-weight-bold">Ksh ${parseFloat(win).toFixed(2)}</span></td>
  `;
  if (tbody.rows.length > 5) tbody.deleteRow(5);
}
</script>

<?php require_once 'includes/footer.php'; ?>
