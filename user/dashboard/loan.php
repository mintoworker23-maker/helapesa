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

<style>
  .loan-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-bottom: 30px;
  }

  .loan-radio {
    display: none;
  }

  .loan-box {
    flex: 1 1 calc(33.333% - 15px);
    min-width: 120px;
    height: 100px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background: white;
    transition: all 0.3s ease;
    padding: 10px;
  }

  .loan-box:hover {
    border-color: #1A73E8;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  }

  .loan-radio:checked + .loan-box {
    background-image: var(--primary-gradient);
    border-color: transparent;
    color: white !important;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
  }

  .loan-radio:checked + .loan-box h5 {
    color: white !important;
  }

  #loanDetails {
    background: #f8f9fa;
    border-radius: 15px;
    border: 1px solid rgba(0,0,0,0.05);
    display: none;
    animation: fadeIn 0.4s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .detail-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px dashed #dee2e6;
  }

  .detail-item:last-child {
    border-bottom: none;
  }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card feature-card overflow-hidden">
                <div class="card-header bg-gradient-dark p-4 text-center">
                    <h3 class="text-white mb-0 font-weight-bolder">Apply for a Loan</h3>
                    <p class="text-white opacity-8 mb-0">Select an amount and get instant approval</p>
                </div>
                
                <div class="card-body p-4">
                    <form id="loanForm">
                        <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Choose Loan Amount</h6>
                        
                        <div class="loan-options">
                          <?php
                            $loanOptions = [500, 1000, 2000, 5000, 10000, 20000];
                            foreach ($loanOptions as $amount):
                          ?>
                            <label class="mb-0 w-100 h-100" style="flex: 1 1 calc(33.333% - 15px); min-width: 120px;">
                                <input type="radio" name="loan_amount" value="<?= $amount ?>" class="loan-radio">
                                <div class="loan-box">
                                    <h5 class="mb-0 font-weight-bolder">Ksh <?= number_format($amount) ?></h5>
                                </div>
                            </label>
                          <?php endforeach; ?>
                        </div>

                        <div id="loanDetails" class="p-4 mb-4">
                            <h6 class="font-weight-bold mb-3">Loan Summary</h6>
                            <div class="detail-item">
                                <span class="text-muted">Loan Amount:</span>
                                <span class="font-weight-bold" id="selectedAmount">Ksh 0</span>
                            </div>
                            <div class="detail-item">
                                <span class="text-muted">Repayment Duration:</span>
                                <span class="font-weight-bold">30 Days</span>
                            </div>
                            <div class="detail-item">
                                <span class="text-muted">Interest Rate:</span>
                                <span class="text-info font-weight-bold">10%</span>
                            </div>
                            <hr class="my-2">
                            <div class="detail-item">
                                <span class="text-dark font-weight-bold">Total to Repay:</span>
                                <span class="text-primary font-weight-bolder h5 mb-0" id="repayAmount">Ksh 0</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark btn-custom w-100 mt-2">
                            <span class="material-symbols-rounded align-middle me-1">account_balance_wallet</span> 
                            PROCEED WITH APPLICATION
                        </button>
                    </form>
                </div>
                
                <div class="card-footer bg-gray-100 p-3 text-center">
                    <p class="text-xs text-muted mb-0">
                        <i class="material-symbols-rounded text-xs align-middle">info</i>
                        By clicking "Proceed", you agree to our loan terms and conditions.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  const radios = document.querySelectorAll('.loan-radio');
  const detailsBox = document.getElementById('loanDetails');
  const selectedAmount = document.getElementById('selectedAmount');
  const repayAmount = document.getElementById('repayAmount');

  radios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.checked) {
        const amount = parseFloat(radio.value);
        const repay = amount + amount * 0.10;

        selectedAmount.textContent = `Ksh ${amount.toLocaleString()}`;
        repayAmount.textContent = `Ksh ${repay.toLocaleString()}`;
        detailsBox.style.display = 'block';
      }
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
    
    alertify.confirm('Confirm Loan', `Are you sure you want to apply for a loan of Ksh ${amount.toLocaleString()}?`, 
      function() {
        alertify.success(`Loan application for Ksh ${amount.toLocaleString()} submitted!`);
        // TODO: Send data to backend via fetch/AJAX
      },
      function() {
        alertify.error('Application cancelled.');
      }
    );
  });
</script>

<?php include 'includes/footer.php'; ?>