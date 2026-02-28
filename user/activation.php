<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$phone = $_SESSION['phone'] ?? '';

$messages = [
    'activation_error' => $_SESSION['activation_error'] ?? '',
    'register_success' => $_SESSION['register_success'] ?? '',
];

function showMessage($message, $type = 'danger') {
    return !empty($message) ? "<div class='alert alert-{$type}' role='alert'>{$message}</div>" : '';
}
// Unset specific session messages after fetching them
unset($_SESSION['activation_error'], $_SESSION['register_success']);

// Define packages with prices and commission structures
$packages = [
    'basic' => ['price' => 1300, 'name' => 'Basic', 'commissions' => [1 => 600]],
    'silver' => ['price' => 2000, 'name' => 'Silver', 'commissions' => [1 => 500, 2 => 200]],
    'gold' => ['price' => 4000, 'name' => 'Gold', 'commissions' => [1 => 1200, 2 => 500]],
    'premium' => ['price' => 5000, 'name' => 'Premium', 'commissions' => [1 => 1300, 2 => 700]],
];
?>
<!-- HTML code for the activation page -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="user\assets\images\favicon.png">
  <title>
    Earnflow | activation
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- CSS Files -->
  <link id="pagestyle" href="assets/css/soft-ui-dashboard.css" rel="stylesheet" />
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .package-card {
      transition: all 0.3s ease;
      cursor: pointer;
    }
    .package-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .package-card.selected {
      border: 3px solid #17c1e8;
      box-shadow: 0 5px 15px rgba(23, 193, 232, 0.3);
    }
    .commission-level {
      display: inline-block;
      background: #e9f7fe;
      border-radius: 12px;
      padding: 2px 8px;
      font-size: 12px;
      margin-right: 5px;
    }
  </style>
</head>

<body class="">
  <!-- Modal Background -->
<div id="paymanuallyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <!-- Modal Box -->
  <div class="relative bg-white rounded-xl p-6 w-full max-w-sm shadow-xl text-center">
    
    <!-- Close Button -->
    <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 focus:outline-none">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  <!-- Modal Content -->
    <form action="phpscripts/activationconfig.php" method="POST" id="manualPayForm">
      <!-- Hidden fields to pass package & price -->
      <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
      <input type="hidden" name="package" id="manual-package" value="basic">
      <input type="hidden" name="Price" id="manual-price" value="600">
      <input type="hidden" name="pay_method" value="manual">
    <h2 class="text-xl font-semibold mb-4 text-info text-gradient">Pay Manually</h2>
    <div class="text-left text-sm text-gray-700">
    <p class="mb-2">If you want to pay manually, use the following steps:</p>
    <ol class="list-decimal pl-5 space-y-1">
      <li>Open your STK tool or phone app and enter <strong>*334#</strong></li>
      <li>Select <strong>Lipa na M-Pesa</strong></li>
      <li>Select <strong>Buy Goods and Services</strong></li>
      <li>Enter <strong>3723942</strong> as your Till Number</li>
      <li>Pay <strong id="modalPackageAmount">KES 600</li>
      <li>Input the transaction code below and wait for activation</li>
    </ol>
    </div>

    <!-- Transaction Input -->
    <div class="mt-4">
      <label for="transactionalcode" class="block text-sm font-medium text-gray-700 mb-1">Transaction Code</label>
      <input type="text" name="transactionalcode" id="transactionalcode" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Enter your transaction code" required>
    </div>
    <div class="text-center">
        <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">Activate account</button>
      </div>
  </div>
</div>
  <!-- End Modal -->
</form>

<!-- Daraja Loading Modal -->
<div id="darajaLoadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <!-- Modal Box -->
  <div class="relative bg-white rounded-xl p-6 w-full max-w-sm shadow-xl text-center">
    
    <!-- Spinner -->
    <div class="mx-auto mb-4">
      <svg class="animate-spin h-10 w-10 text-cyan-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.372 0 0 5.372 0 12h4z"></path>
      </svg>
    </div>

    <h2 class="text-lg font-semibold text-info text-gradient mb-2">Processing Payment...</h2>
    <p class="text-gray-600 text-sm">Please wait while we initiate the M-Pesa STK Push.<br/>Check your phone to complete payment.</p>
  </div>
</div>

  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain mt-8">
                <div class="card-header pb-0 text-left bg-transparent">
                  <h3 class="text-3xl font-bold text-info text-gradient mb-2">Activation</h3>
                  <p class="mb-0">Activate your account to access your dashboard</p>
                </div>
                <div class="card-body">
                  <form role="form" action="phpscripts/activationconfig.php" method="post" id="darajapayForm">
                    <div id="manualFeedback"></div>

                    <?= showMessage($messages['activation_error'], 'danger') ?>
                    <?= showMessage($messages['register_success'], 'success') ?>
                    <label>Phone Number</label>
                    <div class="mb-3">
                      <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" readonly>
                    </div>
                    
                    <!-- Package Selection Cards -->
                                        <div class="mb-4">
                      <label class="form-label">Package</label>
                      <select id="package" name="package" class="form-control" required>
                        <option value="" disabled selected>Select your package</option>
                        <?php foreach ($packages as $key => $package): ?>
                          <option value="<?= $key ?>"><?= $package['name'] ?> - KES <?= number_format($package['price']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    
                    <input type="hidden" name="Price" id="packagePrice" value="">
                    <input type="hidden" name="pay_method" value="automatic">
                    
                    <div class="text-center">
                      <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0" id="darajaSubmitBtn" >Activate account</button>
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-4 text-sm mx-auto">
                    want to pay manually?
                    <a id="pay-manual-link" class="text-info text-gradient font-weight-bold">Pay manually</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('assets/images/businessman-working-laptop.jpg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!--   Core JS Files   -->
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="assets/js/soft-ui-dashboard.min.js"></script>
  <script>
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      alert.classList.add('fade-out');
      setTimeout(() => alert.remove(), 500);
    });
  }, 5000);
</script>

<script>
  // Modal Elements
  const modal = document.getElementById('paymanuallyModal');
  const closeBtn = document.getElementById('closeModalBtn');
  const payManuallyLink = document.getElementById('pay-manual-link');
  const modalPackageAmount = document.getElementById('modalPackageAmount');
  const manualPackageInput = document.getElementById('manual-package');
  const manualPriceInput = document.getElementById('manual-price');
  const packagePriceInput = document.getElementById('packagePrice');
  const packageSelect = document.getElementById('package');

  // Package data from PHP
  const packages = <?= json_encode($packages) ?>;

  // Update modal fields based on selected package
  packageSelect.addEventListener("change", function () {
    const selected = this.value;
    if (packages[selected]) {
      manualPackageInput.value = selected;
      manualPriceInput.value = packages[selected].price;
      modalPackageAmount.textContent = `KES ${packages[selected].price}`;
      packagePriceInput.value = packages[selected].price;
    }
  });

  // Show manual modal only if package is selected
  payManuallyLink.addEventListener('click', () => {
    const selected = packageSelect.value;
    if (!selected) {
      alert("Please select a package first.");
      return;
    }

    // Update modal with latest selection
    manualPackageInput.value = selected;
    manualPriceInput.value = packages[selected].price;
    modalPackageAmount.textContent = `KES ${packages[selected].price}`;
    modal.classList.remove('hidden');
  });

  // Close modal with close button
  closeBtn.addEventListener('click', () => {
    modal.classList.add('hidden');
  });

  // Close modal by clicking outside
  window.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.add('hidden');
    }
  });

  // Loading modal handlers for Daraja
  function showDarajaLoader() {
    document.getElementById('darajaLoadingModal').classList.remove('hidden');
  }

  function hideDarajaLoader() {
    document.getElementById('darajaLoadingModal').classList.add('hidden');
  }

  // Handle Daraja form submit
  document.getElementById("darajapayForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    showDarajaLoader();

    const formData = new FormData(this);

    try {
      const response = await fetch("phpscripts/activationconfig.php", {
        method: "POST",
        body: formData
      });

      const result = await response.json();
      hideDarajaLoader();

      if (result.status === "success") {
        alert("STK Push sent! Please check your phone to complete payment.");
        setTimeout(() => window.location.href = "login.php", 1500);
      } else {
        alert("Payment Error: " + (result.message || "An error occurred"));
      }

    } catch (error) {
      hideDarajaLoader();
      alert("Network or server error: " + error.message);
    }
  });

  // Auto-dismiss alerts
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      alert.classList.add('fade-out');
      setTimeout(() => alert.remove(), 500);
    });
  }, 5000);
</script>
<script>
document.getElementById("manualPayForm").addEventListener("submit", async function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  const feedback = document.getElementById("manualFeedback");

  // Clear any previous messages
  feedback.innerHTML = '';

  try {
    const response = await fetch("phpscripts/activationconfig.php", {
      method: "POST",
      body: formData
    });

    const result = await response.json();

    if (result.status === "success") {
      feedback.innerHTML = `
        <div class="alert alert-success" role="alert">
          ${result.message}
        </div>`;
        modal.classList.add("hidden");
      setTimeout(() => window.location.href = "login.php", 1500);
    } else {
      feedback.innerHTML = `
        <div class="alert alert-danger" role="alert">
          ${result.message || "Something went wrong"}
        </div>`;
    }

  } catch (error) {
    feedback.innerHTML = `
      <div class="alert alert-danger" role="alert">
        Network error: ${error.message}
      </div>`;
  }
});
</script>

</body>
</html>