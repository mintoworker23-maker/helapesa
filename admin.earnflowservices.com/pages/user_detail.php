<?php
session_start();
require_once '../config/config.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    echo "User ID is required.";
    exit();
}

$user_id = (int) $_GET['id'];

// Fetch user data
$userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

// Referred by username
$referrer = null;
if (is_numeric($user['referred_by'])) {
    $refQuery = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $refQuery->bind_param("i", $user['referred_by']);
    $refQuery->execute();
    $refResult = $refQuery->get_result();
    $referrer = $refResult->fetch_assoc()['username'] ?? null;
}

// User stats
function getUserStat($conn, $query, $user_id) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->fetch();
    return $value ?? 0;
}

$totalEarned = getUserStat($conn, "SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'earn'", $user_id);
$totalWithdrawn = getUserStat($conn, "SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'withdraw'", $user_id);
$totalTransactions = getUserStat($conn, "SELECT COUNT(*) FROM transactions WHERE user_id = ?", $user_id);
$totalReferrals = getUserStat($conn, "SELECT COUNT(*) FROM referals WHERE referrer_id = ?", $user_id);
$totalReferralCommission = getUserStat($conn, "SELECT SUM(bonus_amount) FROM referals WHERE referrer_id = ?", $user_id);
$totalSpinAmount = getUserStat($conn, "SELECT SUM(bet) FROM game_history WHERE user_id = ?", $user_id);
$totalSpinEarned = getUserStat($conn, "SELECT SUM(win) FROM game_history WHERE user_id = ?", $user_id);
$totalTriviaEarned = getUserStat($conn, "SELECT SUM(rewarded_amount) FROM trivia_attempts WHERE user_id = ?", $user_id);
?>

<style>
/* === Profile Card (Left Section) === */
.profile-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 1.5rem;
  text-align: center;
}

.profile-card img {
  width: 100%;
  border-radius: 10px;
  object-fit: cover;
  height: 250px;
}

.profile-card h5 {
  margin-top: 1rem;
  font-weight: 600;
  color: #333;
}

.profile-card p {
  margin: 0.25rem 0;
  color: #666;
  font-size: 0.9rem;
}


/* === Stat Cards === */
.stat-card {
    background-image: linear-gradient(195deg, #42424a 0%, #191919 100%);
  position: relative;
  border-radius: 12px;
  padding: 1.5rem;
  color: white;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: transform 0.2s ease-in-out;
  height: 130px;
}

.stat-card:hover {
  transform: translateY(-5px);
}

/* === Gradient Color Themes === */
.stat-card.blue { background: linear-gradient(135deg, #1e90ff, #007bff); }
.stat-card.red { background: linear-gradient(135deg, #ff6a6a, #ff4747); }
.stat-card.purple { background: linear-gradient(135deg, #7b2ff7, #5116a9); }
.stat-card.navy { background: linear-gradient(135deg, #0f2027, #203a43); }

/* === Icon Background === */
.stat-icon-bg {
  position: absolute;
  bottom: 10px;
  right: 10px;
  font-size: 100px;
  opacity: 0.08;
  z-index: 0;
  line-height: 1;
}

/* === Text Over Icon === */
.stat-content {
  position: relative;
  z-index: 1;
}

.stat-content h6 {
  font-size: 0.9rem;
  text-transform: uppercase;
  opacity: 0.8;
}

.stat-content h4 {
  font-size: 1.5rem;
  margin: 0;
}
</style>

<main class="main-content position-relative border-radius-lg">
  <div class="container-fluid py-4">
    <div class="row">
      <!-- Left: Profile Card -->
      <div class="col-lg-4 mb-4">
        <div class="card shadow profile-card border-0">
          <img src="/user/profile_pictures/<?= $user['profile_picture'] ?: '../assets/images/default-profile.jpg' ?>" alt="Profile">
          <div class="card-body text-start">
            <h5 class="fw-bold"><?= htmlspecialchars($user['username']) ?></h5>
            <p class="text-muted mb-1"><i class="material-symbols-rounded align-middle me-1">phone_iphone</i><?= htmlspecialchars($user['phone']) ?></p>
            <p class="text-muted mb-1"><i class="material-symbols-rounded align-middle me-1">mail</i><?= htmlspecialchars($user['email']) ?></p>
            <p class="text-muted mb-1"><i class="material-symbols-rounded align-middle me-1">calendar_month</i>Joined: <?= date('d M Y', strtotime($user['created_on'])) ?></p>
            <p class="text-muted"><i class="material-symbols-rounded align-middle me-1">group</i>Referred By: <strong><?= $referrer ?? '—' ?></strong></p>
          </div>
        </div>
      </div>

      <!-- Right: Stats Grid -->
      <div class="col-lg-8">
        <div class="row g-4 text-white">
          <?php
          $stats = [
            ['Balance', number_format($user['balance'], 2), 'account_balance_wallet'],
            ['Total Earned', number_format($totalEarned, 2), 'payments'],
            ['Total Withdrawn', number_format($totalWithdrawn, 2), 'credit_card_off'],
            ['Transactions', $totalTransactions, 'receipt_long'],
            ['Referrals', $totalReferrals, 'group'],
            ['Referral Commission', number_format($totalReferralCommission, 2), 'volunteer_activism'],
            ['Spin Amount', number_format($totalSpinAmount, 2), 'casino'],
            ['Spin Earned', number_format($totalSpinEarned, 2), 'military_tech'],
            ['Trivia Earnings', number_format($totalTriviaEarned, 2), 'quiz'],
          ];

          foreach ($stats as [$label, $value, $icon]):
          ?>
          <div class="col-md-6 col-lg-4">
            <div class="card stat-card shadow h-100 border-0">
              <div class="stat-icon-bg">
                <i class="material-symbols-rounded"><?= $icon ?></i>
              </div>
              <div class="card-body stat-content text-center">
                <h6 class="text-uppercase small mb-1 text-white"><?= $label ?></h6>
                <h5 class="fw-bold mb-0 text-white"><?= $value ?></h5>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
