<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$user = getCurrentUser($conn);

// Fetch accurate balance
$stmt = $conn->prepare("SELECT balance, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance, $mpesa_number);
$stmt->fetch();
$stmt->close();

// Eligibility logic
$requiredSpins = 10;
$requiredReferrals = 2;

$spinCount = $referralCount = 0;

$stmt = $conn->prepare("SELECT COUNT(*) FROM game_history WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($spinCount);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($referralCount);
$stmt->fetch();
$stmt->close();
?>

<div class="row">
  <div class="col-lg-11 mx-auto">
    <div class="row">
      <div class="col-lg-15 mb-lg-0 mb-4">
        <div class="card mt-4 text-center p-4">
          <div class="card-header pb-0">
            <h4 class="text-center m-0">Withdraw</h4>
          </div>

          <?php if ($spinCount < $requiredSpins || $referralCount < $requiredReferrals): ?>
            <div class="alert alert-warning mt-3 text-start">
              <strong>Note:</strong> You must complete at least <?= $requiredSpins ?> spins and refer at least <?= $requiredReferrals ?> users to be eligible for withdrawal.<br>
              ✅ Spins: <?= $spinCount ?> / <?= $requiredSpins ?><br>
              ✅ Referrals: <?= $referralCount ?> / <?= $requiredReferrals ?>
            </div>
          <?php endif; ?>

          <div class="bg-gradient-dark shadow border-radius-lg p-3 mb-3 mt-3">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div class="d-flex align-items-center mb-3 mb-md-0">
              <div class="icon d-flex align-items-center justify-content-center bg-dark me-3"
                style="width: 60px; height: 60px; border-radius: 50%; color: white;">
                <i class="material-symbols-rounded" style="font-size: 36px;">account_balance</i>
              </div>
              <div class="text-white">
                <h5 class="text-white mb-0">Balance: <strong>Ksh <?= number_format($balance, 2) ?></strong></h5>
              </div>
            </div>

            <div class="w-100 w-md-auto">
              <button class="btn btn-lg mt-4 w-100 bg-gradient-dark w-100 w-md-auto" style="min-width: 200px;" data-bs-toggle="modal" data-bs-target="#depositModal">
                Deposit
              </button>
            </div>
          </div>
        </div>

          <form method="POST" action="../phpscripts/walletsconfig.php" id="withdrawForm">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <input type="text" class="form-control border border-2 border-dark text-center px-2"  id="phoneNumber" value="+254<?= htmlspecialchars($mpesa_number) ?>" readonly> 
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="text" class="form-control border border-2 border-dark text-center px-2" name="amount" placeholder="e.g. 1000">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-lg btn-primary mt-4 w-100 bg-dark">Withdraw</button>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Transactions Section -->
<div class="row">
  <div class="col-sm-11 mt-4 mx-auto">
    <div class="card h-100 mb-4">
      <div class="card-header pb-0 px-3">
        <div class="row">
          <div class="col-md-6"><h6 class="mb-0">Your Transactions</h6></div>
          <div class="col-md-6 d-flex justify-content-start justify-content-md-end align-items-center">
            <i class="material-symbols-rounded me-2 text-lg">date_range</i>
            <small><?= date('F Y') ?></small>
          </div>
        </div>
      </div>
      <div class="card-body pt-4 p-3" id="transactionsContainer"></div>
    </div>
  </div>
</div>

<!-- Alertify JS -->


<!-- Session Flash Messages (Success/Error Alerts) -->
<script>
  alertify.set('notifier','position', 'top-right');

  <?php if (isset($_SESSION['withdraw_success'])): ?>
    setTimeout(() => {
      alertify.success("<?= addslashes($_SESSION['withdraw_success']) ?>");
    }, 300);
    <?php unset($_SESSION['withdraw_success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['withdraw_error'])): ?>
    setTimeout(() => {
      alertify.error("<?= addslashes($_SESSION['withdraw_error']) ?>");
    }, 300);
    <?php unset($_SESSION['withdraw_error']); ?>
  <?php endif; ?>
    <?php if (isset($_SESSION['deposit_success'])): ?>
    setTimeout(() => {
      alertify.success("<?= addslashes($_SESSION['deposit_success']) ?>");
    }, 300);
    <?php unset($_SESSION['deposit_success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['deposit_error'])): ?>
    setTimeout(() => {
      alertify.error("<?= addslashes($_SESSION['deposit_error']) ?>");
    }, 300);
    <?php unset($_SESSION['deposit_error']); ?>
  <?php endif; ?>

</script>

<!-- Form Validation & Transactions -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('withdrawForm');
    if (form) {
      form.addEventListener('submit', function(e) {
        const amountInput = document.querySelector('input[name="amount"]');
        const amount = parseFloat(amountInput.value.trim());

        const balance = <?= $balance ?>;
        const spinCount = <?= $spinCount ?>;
        const requiredSpins = <?= $requiredSpins ?>;
        const referralCount = <?= $referralCount ?>;
        const requiredReferrals = <?= $requiredReferrals ?>;

        if (isNaN(amount) || amount <= 0) {
          e.preventDefault();
          alertify.error("Please enter a valid amount.");
          return;
        }

        if (amount > balance) {
          e.preventDefault();
          alertify.error("Insufficient funds.");
          return;
        }

        if (spinCount < requiredSpins) {
          e.preventDefault();
          alertify.error(`You need at least ${requiredSpins} spins.`);
          return;
        }

        if (referralCount < requiredReferrals) {
          e.preventDefault();
          alertify.error(`You need at least ${requiredReferrals} referrals.`);
          return;
        }

        // All validations passed — show message and delay submit
        e.preventDefault();
        alertify.message("Submitting withdrawal...");
        setTimeout(() => {
          form.submit();
        }, 700);
      });
    }

    // Load transactions
    fetch('../phpscripts/gettransactions.php')
      .then(res => res.json())
      .then(data => {
        const container = document.getElementById('transactionsContainer');
        if (!data || data.length === 0) {
          container.innerHTML = '<p class="text-muted text-center">No transactions yet.</p>';
          return;
        }

        let html = '';
        data.forEach(tx => {
          const color = tx.type === 'withdraw' ? 'danger' : 'success';
          const sign = tx.type === 'withdraw' ? '-' : '+';
          const formattedAmount = Number(tx.amount).toLocaleString('en-KE', {
            style: 'currency',
            currency: 'KES'
          });

          html += `
            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
              <div>
                <h6 class="mb-1">${tx.description}</h6>
                <small class="text-muted">${new Date(tx.created_at).toLocaleString()}</small>
              </div>
              <div>
                <span class="text-${color}">${sign}${formattedAmount}</span>
              </div>
            </div>
          `;
        });

        container.innerHTML = html;
      })
      .catch(() => {
        document.getElementById('transactionsContainer').innerHTML = `<p class="text-danger">Could not load transactions.</p>`;
      });
  });
</script>



<?php include('includes/footer.php'); ?>