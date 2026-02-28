<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<style>
  .loan-wrapper {
  padding: 0 16px; /* Adds space on left and right */
}

  .loan-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}

  .loan-box {
    width: calc(50% - 10px);
    min-width: 140px;
    flex: 1 1 calc(50% - 10px);
    height: 120px;
    border: 2px solid #ccc;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
    background: white;
    color: black;
    transition: all 0.2s ease-in-out;
  }

  .loan-box.active {
    background: black;
    color: white;
    border-color: black;
  }

  @media (max-width: 600px) {
    .loan-box {
      flex: 1 1 calc(50% - 10px);
    }
  }

  #loanDetails {
    display: none;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 1rem;
  }

  button[type="submit"] {
    width: 100%;
    padding: 12px;
    font-size: 18px;
    background-color: black;
    color: white;
    border: none;
    border-radius: 8px;
  }
</style>

<div class="main-content">
  <h2 style="margin-bottom: 1rem; margin-left: 3rem;" class="justify-content-center align-content-center">Apply for a Loan</h2>

  <form id="loanForm">
    <div class="loan-wrapper">
    <div class="loan-options">
      <?php
        $loanOptions = [500, 1000, 2000, 5000, 10000];
        foreach ($loanOptions as $amount):
      ?>
        <label class="loan-box">
          <input type="radio" name="loan_amount" value="<?= $amount ?>" style="display:none;">
          Ksh <?= number_format($amount) ?>
        </label>
      <?php endforeach; ?>
    </div>

    <div id="loanDetails">
      <p><strong>Loan Amount:</strong> <span id="selectedAmount"></span></p>
      <p><strong>Repayment Duration:</strong> 30 Days</p>
      <p><strong>Interest:</strong> 10%</p>
      <p><strong>Total to Repay:</strong> <span id="repayAmount"></span></p>
    </div>

    <button type="submit" style="margin-top: 2rem;">Apply Loan</button>
        </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script>
  const radios = document.querySelectorAll('input[name="loan_amount"]');
  const boxes = document.querySelectorAll('.loan-box');
  const detailsBox = document.getElementById('loanDetails');
  const selectedAmount = document.getElementById('selectedAmount');
  const repayAmount = document.getElementById('repayAmount');

  boxes.forEach(box => {
    box.addEventListener('click', () => {
      boxes.forEach(b => b.classList.remove('active'));
      const radio = box.querySelector('input');
      radio.checked = true;
      box.classList.add('active');

      const amount = parseFloat(radio.value);
      const repay = amount + amount * 0.10;

      selectedAmount.textContent = `Ksh ${amount.toLocaleString()}`;
      repayAmount.textContent = `Ksh ${repay.toLocaleString()}`;
      detailsBox.style.display = 'block';
    });
  });

  document.getElementById('loanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const chosen = document.querySelector('input[name="loan_amount"]:checked');
    if (!chosen) {
      alertify.error('Please select a loan amount.');
      return;
    }

    const amount = parseFloat(chosen.value);
    alertify.success(`Loan application for Ksh ${amount.toLocaleString()} submitted!`);

    // TODO: Send data to backend via fetch/AJAX
  });
</script>

<?php include 'includes/footer.php'; ?>