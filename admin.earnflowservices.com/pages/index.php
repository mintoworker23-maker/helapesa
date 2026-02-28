<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php';

// Fetching statistics
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalActivated = $conn->query("SELECT COUNT(*) AS total FROM users WHERE is_active = 1")->fetch_assoc()['total'];
$pendingActivations = $conn->query("SELECT COUNT(*) AS total FROM users WHERE is_active = 0 AND amount_paid > 0")->fetch_assoc()['total'];
$totalUserBalance = $conn->query("SELECT SUM(balance) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$totalEarned = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type = 'earn'")->fetch_assoc()['total'] ?? 0;
$totalWithdrawn = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type = 'withdraw'")->fetch_assoc()['total'] ?? 0;
$totalReferral = $conn->query("SELECT SUM(bonus_amount) AS total FROM referals")->fetch_assoc()['total'] ?? 0;
$pendingWithdrawals = $conn->query("SELECT COUNT(*) AS total FROM withdrawals WHERE status = 'pending'")->fetch_assoc()['total'];
$spins = $conn->query("SELECT COUNT(*) AS total FROM game_history")->fetch_assoc()['total'] ?? 0;

// Recent Data
$recentUsers = $conn->query("SELECT username, phone, created_on FROM users ORDER BY created_on DESC LIMIT 5");
$recentWithdrawals = $conn->query("SELECT w.amount, w.requested_at, u.username FROM withdrawals w JOIN users u ON u.id = w.user_id WHERE w.status = 'pending' ORDER BY w.requested_at DESC LIMIT 5");
$recentActivations = $conn->query("SELECT username, phone, created_on FROM users WHERE is_active = 0 AND amount_paid > 0 ORDER BY created_on DESC LIMIT 5");

$activationIncome = $conn->query("SELECT SUM(amount_paid) AS total FROM users WHERE is_active = 1")->fetch_assoc()['total'] ?? 0;
$referralPayout = $conn->query("SELECT SUM(bonus_amount) AS total FROM referals")->fetch_assoc()['total'] ?? 0;
$platformProfit = $activationIncome - $referralPayout;

$growthData = $conn->query("
  SELECT DATE(created_on) AS date,
         COUNT(*) AS registered,
         SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS activated
  FROM users
  GROUP BY DATE(created_on)
  ORDER BY DATE(created_on) DESC
  LIMIT 7
");

$dates = $reg = $active = [];
while ($row = $growthData->fetch_assoc()) {
    $dates[] = $row['date'];
    $reg[] = (int)$row['registered'];
    $active[] = (int)$row['activated'];
}


// Chart Data - earnings vs withdrawals
$chartData = $conn->query("SELECT DATE(created_at) as date,
  SUM(CASE WHEN type = 'earn' THEN amount ELSE 0 END) as earned,
  SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END) as withdrawn
  FROM transactions
  GROUP BY DATE(created_at)
  ORDER BY DATE(created_at) DESC
  LIMIT 7");

$labels = [];
$earned = [];
$withdrawn = [];
while ($row = $chartData->fetch_assoc()) {
  $labels[] = $row['date'];
  $earned[] = $row['earned'];
  $withdrawn[] = $row['withdrawn'];
}
?>

<div class="container-fluid py-4">
  <div class="row">
    <?php
      $stats = [
        ['Total Users', $totalUsers, 'group', 'dark'],
        ['Activated Users', $totalActivated, 'verified_user', 'success'],
        ['Pending Activations', $pendingActivations, 'hourglass_top', 'warning'],
        ['Activation Income', 'Ksh ' . number_format($activationIncome), 'attach_money', 'info'],
        ['Platform Profit', 'Ksh ' . number_format($platformProfit), 'trending_up', 'primary'],
        ['Referral Payouts', 'Ksh ' . number_format($referralPayout), 'people_alt', 'danger'],
        ['Total Earned', 'Ksh ' . number_format($totalEarned), 'payments', 'info'],
        ['Total Withdrawn', 'Ksh ' . number_format($totalWithdrawn), 'payment', 'primary'],
        ['Total Referral Bonus', 'Ksh ' . number_format($totalReferral), 'diversity_3', 'danger'],
        ['Total Balance', 'Ksh ' . number_format($totalUserBalance), 'account_balance_wallet', 'secondary'],
        ['Pending Withdrawals', $pendingWithdrawals, 'price_check', 'warning'],
        ['Spins Logged', $spins, 'casino', 'primary']
      ];
      foreach ($stats as [$label, $value, $icon, $color]) {
        echo "<div class='col-md-4 mb-3'><div class='card'><div class='card-body d-flex'><div class='icon icon-shape bg-gradient-$color text-white text-center border-radius-md'><i class='material-symbols-rounded'>$icon</i></div><div class='ms-3'><p class='mb-0 text-sm'>$label</p><h6 class='mb-0'>$value</h6></div></div></div></div>";
      }
    ?>
  </div>

  <!-- CHART -->
  <div class="row mb-4">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header pb-0"><h6>Earnings vs Withdrawals (Last 7 Days)</h6></div>
        <div class="card-body">
          <canvas id="earnChart" height="100"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-4">
  <div class="card-header"><h6>Registrations vs Activations (Last 7 Days)</h6></div>
  <div class="card-body">
    <canvas id="userGrowthChart" height="100"></canvas>
  </div>
</div>


  <!-- TABLES -->
    <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h6>Newly Registered Users</h6>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Username</th>
              <th>Phone</th>
              <th>Date Registered</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
            $latestUsers = $conn->query("SELECT username, phone, created_on, is_active FROM users ORDER BY created_on DESC LIMIT 5");
            while ($user = $latestUsers->fetch_assoc()):
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['phone']) ?></td>
              <td><?= date('d M Y', strtotime($user['created_on'])) ?></td>
              <td>
                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                  <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    </div>


    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-header">
          <h6>Recent Pending Withdrawals</h6>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>User</th>
                <th>M-Pesa</th>
                <th>Amount</th>
                <th>Requested At</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $i = 1;
              $withdrawals = $conn->query("
                SELECT w.id, w.amount, w.requested_at, u.phone, u.username 
                FROM withdrawals w 
                JOIN users u ON u.id = w.user_id 
                WHERE w.status = 'pending' 
                ORDER BY w.requested_at DESC 
                LIMIT 5
              ");
              while ($w = $withdrawals->fetch_assoc()):
              ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($w['username']) ?></td>
                <td><?= htmlspecialchars($w['phone']) ?></td>
                <td>Ksh <?= number_format($w['amount']) ?></td>
                <td><?= date('d M Y, h:i A', strtotime($w['requested_at'])) ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
          <div class="text-end mt-2">
            <a href="withdrawals.php" class="btn btn-sm btn-outline-primary">View All Withdrawals</a>
          </div>
        </div>
      </div>
    </div>


<div class="col-12 mb-4">
  <div class="card">
    <div class="card-header">
      <h6>Pending Manual Activations</h6>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Phone</th>
            <th>Package</th>
            <th>Amount Paid</th>
            <th>Tx Code</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          $activations = $conn->query("
            SELECT username, phone, package, amount_paid, transaction_code 
            FROM users 
            WHERE is_active = 0 AND pay_method = 'manual' 
            ORDER BY created_on DESC 
            LIMIT 5
          ");
          while ($u = $activations->fetch_assoc()):
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['phone']) ?></td>
            <td><?= strtoupper($u['package']) ?></td>
            <td>Ksh <?= number_format($u['amount_paid']) ?></td>
            <td><?= htmlspecialchars($u['transaction_code']) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <div class="text-end mt-2">
        <a href="activations.php" class="btn btn-sm btn-outline-primary">Review All Activations</a>
      </div>
    </div>
  </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('earnChart').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode(array_reverse($labels)) ?>,
      datasets: [
        {
          label: 'Earnings',
          data: <?= json_encode(array_reverse($earned)) ?>,
          borderColor: 'green',
          fill: false
        },
        {
          label: 'Withdrawals',
          data: <?= json_encode(array_reverse($withdrawn)) ?>,
          borderColor: 'red',
          fill: false
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {legend: {position: 'top'}},
      scales: {y: {beginAtZero: true}}
    }
  });
</script>
<script>
const ctx2 = document.getElementById('userGrowthChart').getContext('2d');
new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_reverse($dates)) ?>,
    datasets: [
      {
        label: 'Registered',
        data: <?= json_encode(array_reverse($reg)) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.6)'
      },
      {
        label: 'Activated',
        data: <?= json_encode(array_reverse($active)) ?>,
        backgroundColor: 'rgba(75, 192, 192, 0.6)'
      }
    ]
  },
  options: {
    responsive: true,
    scales: {y: {beginAtZero: true}},
    plugins: {legend: {position: 'top'}}
  }
});
</script>


<?php require_once '../includes/footer.php'; ?>
