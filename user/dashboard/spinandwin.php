<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php';

// Simulate a user login (for demo/testing purposes)
if (!isset($_SESSION['user_id'])) {
  $_SESSION['user_id'] = 1; // Replace with actual login logic
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

<h2 class="md-3" style="text-align:center;">Fortune Spin Wheel</h2>

<!-- Main Container -->
<div class=" container d-flex justify-content-space-evenly align-items-center md-3" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; margin-top: 20px;">
  <!-- LEFT: SPIN WHEEL -->
  <div class="card justify-content-space-even" style="position: relative; width: 400px; height: auto;">
    <!-- Pointer Triangle -->
    <div style="
      position: absolute;
      top: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 0;
      border-left: 15px solid transparent;
      border-right: 15px solid transparent;
      border-bottom: 25px solid red;
      z-index: 10;
    "></div>

    <!-- Actual Canvas -->
    <canvas id="wheelCanvas" width="400" height="400"></canvas>
  </div>

  <!-- RIGHT: BET & HISTORY -->
  <div class="card px-3 justify-content-center align-content-center" style="max-width: 300px; width: auto;">
    <div class="card-header text-center text-dark">
      <p><h3>Balance: <br>Ksh <span id="currentBalance"><?= number_format($balance) ?></span></h3></p>
    </div>
    <!-- BETTING -->
    <div class=" col-md-12" style="margin-bottom: 20px;">
      <label for="betAmount"><strong>Enter Your Bet (Ksh):</strong></label><br>
      <input type="number" id="betAmount" placeholder="e.g. 20" min="1" style="width: 100%; padding: 8px; margin-top: 5px;" class="border border-rounded"/>
      <button id="spinBtn" style="margin-top: 10px; width: 100%; padding: 10px;" class="btn btn-lg btn-primary mt-4 bg-dark" >SPIN</button>
    </div>
</div>

    <!-- HISTORY -->
    <div class="col-md-12 justify-content-center align-content-center">
        <div>
            <h4 class="align-content-center">Recent Plays</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Bet (Ksh)</th>
                        <th>Multiplier</th>
                        <th>Win (Ksh)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?php echo $h['bet']; ?></td>
                        <td><?php echo $h['multiplier']; ?>x</td>
                        <td><?php echo $h['win']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
    </div>
  </div>
</div>



<audio id="winSound" src="assets/sounds/win.mp3" preload="auto"></audio>
<audio id="loseSound" src="assets/sounds/lose.mp3" preload="auto"></audio>


<!-- ALERTIFY -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<!-- Winwheel + GSAP -->
<script src="https://cdn.jsdelivr.net/gh/zarocknz/javascript-winwheel/Winwheel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<script>
const segments = [
  { text: '0x', fillStyle: '#f44336' },
  { text: '0.5x', fillStyle: '#9c27b0' },
  { text: '1x', fillStyle: '#3f51b5' },
  { text: '2x', fillStyle: '#2196f3' },
  { text: '5x', fillStyle: '#4caf50' },
  { text: '10x', fillStyle: '#ff9800' }
];

let theWheel = new Winwheel({
  'canvasId': 'wheelCanvas',
  'numSegments': segments.length,
  'outerRadius': 180,
  'segments': segments,
  'animation': {
    'type': 'spinToStop',
    'duration': 5,
    'spins': 8,
    'callbackFinished': showResult,
    'callbackAfter': null // Resets per spin
  }
});

let betAmount = 0;

document.getElementById('spinBtn').addEventListener('click', function () {
  betAmount = parseFloat(document.getElementById('betAmount').value);
  let currentBalance = parseFloat(document.getElementById('currentBalance').innerText.replace(/,/g, ''));

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

  fetch('../phpscripts/get_spin_result.php')
    .then(res => res.json())
    .then(data => {
      if (data.status !== 'success') {
        alertify.error("Could not get spin result.");
        btn.disabled = false;
        return;
      }

      const multiplierText = data.multiplier;
      const targetSegmentIndex = segments.findIndex(seg => seg.text === multiplierText);

      if (targetSegmentIndex === -1) {
        alertify.error("Invalid multiplier from server.");
        btn.disabled = false;
        return;
      }

      // Reset wheel completely
      theWheel.stopAnimation(false);
      theWheel.rotationAngle = 0;
      theWheel.draw();
      theWheel.animation.spins = 8;
      theWheel.animation.duration = 5;
      theWheel._selectedMultiplier = multiplierText;

      // Use segment's stopAngle for accurate spin
      theWheel.animation.stopAngle = theWheel.getRandomForSegment(targetSegmentIndex + 1);
      theWheel.startAnimation();
    })
    .catch(err => {
      alertify.error("Spin failed. Check connection.");
      console.error(err);
      btn.disabled = false;
    });
});

function showResult(indicatedSegment) {
  const multiplier = parseFloat(theWheel._selectedMultiplier.replace('x', ''));
  const winAmount = Math.round(betAmount * multiplier);

  if (multiplier === 0) {
    alertify.error(`💀 You lost your Ksh ${betAmount}`);
    document.getElementById('loseSound').play();
  } else {
    alertify.success(`🎉 You won Ksh ${winAmount} (${indicatedSegment.text})`);
    document.getElementById('winSound').play();
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
      alertify.message(`Balance updated: Ksh ${data.new_balance}`);
      document.getElementById('currentBalance').innerText = data.new_balance.toLocaleString();
      updateHistory(betAmount, multiplier, winAmount);
    } else {
      alertify.error(data.message);
    }

    // Enable the button again
    document.getElementById('spinBtn').disabled = false;
  });
}

function updateHistory(bet, multiplier, win) {
  const table = document.querySelector('table tbody');
  const row = table.insertRow(0);
  row.innerHTML = `
    <td>${bet}</td>
    <td>${multiplier}x</td>
    <td>${win}</td>
  `;
  if (table.rows.length > 5) table.deleteRow(5);
}
</script>


<?php require_once 'includes/footer.php'; ?>