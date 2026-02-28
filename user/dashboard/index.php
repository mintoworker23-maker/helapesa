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
            Check the sales, value and bounce rate by country.
          </p>
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
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
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