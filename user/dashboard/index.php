<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}  // Include the wallet configuration script
require_once '../phpscripts/dashboardconfig.php'; // Include the wallet configuration script
require_once '../phpscripts/referalconfig.php'; // Include the database configuration script

$messages = [
    'login_error' => $_SESSION['login_error'] ?? '',
    'logout_message' => $_SESSION['logout_message'] ?? '',
    'activation_error' => $_SESSION['activation_error'] ?? '',
    'login_success' => $_SESSION['login_success'] ?? '',
];
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "Please log in to access the dashboard.";
    header("Location: ../login.php");
    exit();
}
// Check if the user is activated
if (!isset($_SESSION['activated']) || $_SESSION['activated'] !== true) {
    $_SESSION['activation_error'] = "Please activate your account to access the dashboard.";
    header("Location: ../activation.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Active Notifications
$notifications = $conn->query("SELECT message FROM notifications WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3");

// Fetch Active Bonuses not yet claimed by user
$bonuses = $conn->query("
    SELECT * FROM scheduled_bonuses 
    WHERE status = 'active' 
    AND start_time <= NOW() AND end_time > NOW()
    AND id NOT IN (SELECT bonus_id FROM claimed_bonuses WHERE user_id = $user_id)
");

function showMessage($message, $type = 'danger') {
    return !empty($message) ? "<div id='login-alert' class='alert alert-{$type}' role='alert'>{$message}</div>" : '';
}

//wallet config
?>
<?php if (isset($_SESSION['welcome_bonus'])): ?>
  <script>
    alertify.success("<?= addslashes($_SESSION['welcome_bonus']) ?>");
  </script>
  <?php unset($_SESSION['welcome_bonus']); ?>
<?php endif; ?>

<?php include('includes/header.php');?>
        <div class="container-fluid py-2">
      <div class="row">
        <div class="ms-3">
          <h3 class="mb-0 h4 font-weight-bolder">Dashboard</h3>
          <p class="mb-4">
            Welcome back! Check your progress and latest updates.
          </p>
        </div>

        <!-- 📢 Notifications & Bonuses Section -->
        <div class="col-12 mb-4">
            <?php if ($notifications->num_rows > 0 || $bonuses->num_rows > 0): ?>
                <?php while ($notif = $notifications->fetch_assoc()): ?>
                    <div class="alert alert-info alert-dismissible text-white fade show border-0 shadow-sm mb-2" role="alert">
                        <span class="alert-icon align-middle me-2">
                            <i class="material-symbols-rounded text-md">notifications</i>
                        </span>
                        <span class="alert-text"><strong>Announcement:</strong> <?= htmlspecialchars($notif['message']) ?></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endwhile; ?>

                <?php while ($bonus = $bonuses->fetch_assoc()): ?>
                    <div class="alert alert-success alert-dismissible text-white fade show border-0 shadow-sm mb-2" role="alert">
                        <span class="alert-icon align-middle me-2">
                            <i class="material-symbols-rounded text-md">card_giftcard</i>
                        </span>
                        <span class="alert-text"><strong>Bonus:</strong> <?= htmlspecialchars($bonus['title']) ?> (<?= $bonus['type'] == 'free_spin' ? (int)$bonus['amount'].' Free Spins' : 'Ksh '.number_format($bonus['amount'], 2) ?>)</span>
                        <form method="POST" action="../phpscripts/claim_bonus.php" style="display:inline;">
                            <input type="hidden" name="bonus_id" value="<?= $bonus['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-white ms-3 mb-0">Claim Now</button>
                        </form>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-light border-0 shadow-sm mb-2" role="alert">
                    <span class="alert-text text-dark">
                        <i class="material-symbols-rounded text-md align-middle me-2">info</i>
                        Want to earn more? Try our <strong><a href="tiktok.php" class="text-dark border-bottom">TikTok Videos</a></strong> or <strong><a href="trivia.php" class="text-dark border-bottom">Trivia</a></strong> section!
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Wallet Balance</p>
                  <h4 class="mb-0">Ksh <?= $dashboard_data['current_balance'] ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">weekend</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+55% </span>than last week</p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Total Earned</p>
                  <h4 class="mb-0">Ksh <?= $dashboard_data['total_earned'] ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">weekend</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+5% </span>than yesterday</p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Total Withdrawn</p>
                  <h4 class="mb-0">Ksh <?= $dashboard_data['total_withdrawn'] ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">weekend</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+5% </span>than yesterday</p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Referals</p>
                  <h4 class="mb-0"><?= $dashboard_data['referral_count'] ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">person</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+3% </span>than last month</p>
            </div>
          </div>
        </div>
      </div>
      <script>
      const earningTrendData = <?= json_encode($dashboard_data['earning_trend']) ?>;
      const referralTrendData = <?= json_encode($dashboard_data['referral_trend']) ?>;
      const leaderboardData = <?= json_encode($dashboard_data['leaderboard']) ?>;
      </script>
      <div class="row">
        <div class="col-sm-4 col-md-6 mt-4 mb-4">
          <div class="card">
            <div class="card-body">
              <h6 class="mb-0 ">Earning Trend</h6>
              <p class="text-sm ">Last Campaign Performance</p>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm"> campaign sent 2 days ago </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-4 col-md-6 mt-4 mb-4">
          <div class="card ">
            <div class="card-body">
              <h6 class="mb-0 "> Referals Trend </h6>
              <p class="text-sm "> (<span class="font-weight-bolder">+15%</span>) increase in today sales. </p>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="chart-line" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm"> updated 4 min ago </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-4">
        <div class="col-lg-12 col-md-6 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h4>Leaderboard</h4>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1">Top 5</span> this month
                  </p>
                </div>
                <div class="col-lg-6 col-5 my-auto text-end">
                  <div class="dropdown float-lg-end pe-4">
                    <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-ellipsis-v text-secondary"></i>
                    </a>
                    <ul class="dropdown-menu px-2 py-3 ms-sm-n4 ms-n5" aria-labelledby="dropdownTable">
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Action</a></li>
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Another action</a></li>
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Something else here</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Leaderboard</h5>
                <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1">Top 5</span> this month
                </p>
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="leaderboardTable">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Username</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Earnings</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($dashboard_data['leaderboard'] as $user): ?>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="assets/images/default-profile.jpg" class="avatar avatar-sm me-3" alt="user1">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($user['username']) ?></h6>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold">Ksh <?= number_format($user['total'], 2) ?></span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
        </div>
  <script>
    // Initialize Alertify
    alertify.set('notifier','position', 'top-right');

    // Display session messages
    <?php if (isset($_SESSION['login_success'])): ?>
        alertify.success("<?= addslashes($_SESSION['login_success']) ?>");
        <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['activation_error'])): ?>
        alertify.error("<?= addslashes($_SESSION['activation_error']) ?>");
        <?php unset($_SESSION['activation_error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['withdraw_success'])): ?>
        alertify.success("<?= addslashes($_SESSION['withdraw_success']) ?>");
        <?php unset($_SESSION['withdraw_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['withdraw_error'])): ?>
        alertify.error("<?= addslashes($_SESSION['withdraw_error']) ?>");
        <?php unset($_SESSION['withdraw_error']); ?>
    <?php endif; ?>

    // Example: Show notification when leaderboard is updated
    document.getElementById('dropdownTable')?.addEventListener('click', function() {
        alertify.message('Loading leaderboard data...');
    });

    // Example: Show notification when charts update
    function simulateChartUpdate() {
        alertify.message('Updating chart data...');
        setTimeout(() => alertify.success('Charts updated successfully!'), 1500);
    }

    // Call this when your charts actually update
    // simulateChartUpdate();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const barCtx = document.getElementById('chart-bars').getContext('2d');
  new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: earningTrendData.map(e => e.date),
      datasets: [{
        label: 'Earnings (Ksh)',
        data: earningTrendData.map(e => e.total),
        backgroundColor: '#4CAF50'
      }]
    }
  });

  const lineCtx = document.getElementById('chart-line').getContext('2d');
  new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: referralTrendData.map(r => r.date),
      datasets: [{
        label: 'Referrals',
        data: referralTrendData.map(r => r.count),
        borderColor: '#03A9F4',
        fill: false
      }]
    }
  });
});
</script>
<?php include('includes/footer.php');?>