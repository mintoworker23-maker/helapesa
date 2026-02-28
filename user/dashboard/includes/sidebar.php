<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
$package = strtolower(trim($_SESSION['package'] ?? 'basic'));

// Database Fetch for Dynamic Permissions
$pkg_access_trivia = 0;
$pkg_access_adverts = 0;
$pkg_access_youtube = 0;
$pkg_access_social_media = 0;
$pkg_access_spin_win = 1;

if (isset($conn)) {
    // If $conn is available from parent script
    $stmt = $conn->prepare("SELECT * FROM packages WHERE LOWER(name) = ?"); // Use prepared statement
    if ($stmt) {
        $stmt->bind_param("s", $package);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $pkg_access_trivia = (int)($row['access_trivia'] ?? 0);
            $pkg_access_adverts = (int)($row['access_adverts'] ?? 0);
            $pkg_access_youtube = (int)($row['access_youtube'] ?? 0);
            $pkg_access_social_media = (int)($row['access_social_media'] ?? 0);
            $pkg_access_spin_win = isset($row['access_spin_win']) ? (int)$row['access_spin_win'] : 1;
        } else {
            // Fallback for hardcoded/legacy packages if not found in table
            if (in_array($package, ['gold', 'premium'])) {
                 $pkg_access_trivia = 1;
            }
            if ($package === 'premium') {
                 $pkg_access_adverts = 1;
                 $pkg_access_youtube = 1;
                 $pkg_access_social_media = 1;
            }
        }
    } else {
        if (in_array($package, ['gold', 'premium'])) $pkg_access_trivia = 1;
        if ($package === 'premium') {
            $pkg_access_adverts = 1;
            $pkg_access_youtube = 1;
            $pkg_access_social_media = 1;
        }
    }
} else {
    // Fallback if no DB connection (shouldn't happen in proper structure)
    if (in_array($package, ['gold', 'premium'])) $pkg_access_trivia = 1;
    if ($package === 'premium') {
        $pkg_access_adverts = 1; 
        $pkg_access_youtube = 1;
        $pkg_access_social_media = 1;
    }
}
?>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 my-2" id="sidenav-main">
  <div class="sidenav-header">
    <a class="navbar-brand px-4 py-3 m-0" href="index.php">
      <img src="../assets/images/faviconlight.png" class="logo-dark navbar-brand-img" alt="light_logo" height="30">
      <img src="../assets/images/favicondark.jpg.png" class="logo-light navbar-brand-img" alt="dark_logo" height="30">
    </a>
  </div>

  <hr class="horizontal dark mt-0 mb-2">

  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="index.php">
          <i class="material-symbols-rounded opacity-5">dashboard</i>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'wallet.php' ? 'active' : '' ?>" href="wallet.php">
          <i class="material-symbols-rounded opacity-5">account_balance_wallet</i>
          <span class="nav-link-text ms-1">Wallet</span>
        </a>
      </li>

      <?php if ($pkg_access_spin_win): ?>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'spinandwin.php' ? 'active' : '' ?>" href="spinandwin.php">
          <i class="material-symbols-rounded opacity-5">stadia_controller</i>
          <span class="nav-link-text ms-1">Fortune Spin</span>
        </a>
      </li>
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'referals.php' ? 'active' : '' ?>" href="referals.php">
          <i class="material-symbols-rounded opacity-5">groups</i>
          <span class="nav-link-text ms-1">Referrals</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'loan.php' ? 'active' : '' ?>" href="loan.php">
          <i class="material-symbols-rounded opacity-5">credit_score</i>
          <span class="nav-link-text ms-1">Loans</span>
        </a>
      </li>

      <?php if ($pkg_access_trivia): ?>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'trivia.php' ? 'active' : '' ?>" href="trivia.php">
          <i class="material-symbols-rounded opacity-5">crossword</i>
          <span class="nav-link-text ms-1">Trivias</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($pkg_access_adverts): ?>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'adverts.php' ? 'active' : '' ?>" href="adverts.php">
          <i class="material-symbols-rounded opacity-5">campaign</i>
          <span class="nav-link-text ms-1">Advertisements</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($pkg_access_youtube): ?>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'youtube.php' ? 'active' : '' ?>" href="youtube.php">
          <i class="material-symbols-rounded opacity-5">youtube_activity</i>
          <span class="nav-link-text ms-1">YouTube Videos</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($pkg_access_social_media): ?>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'socialmedia.php' ? 'active' : '' ?>" href="socialmedia.php">
          <i class="material-symbols-rounded opacity-5">mood</i>
          <span class="nav-link-text ms-1">Social Media Ads</span>
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="sidenav-footer position-absolute w-100 bottom-0">
    <div class="mx-3">
      <a class="btn btn-outline-dark mt-4 w-100" href="#" onclick="if(window.Tawk_API){Tawk_API.maximize();} return false;">Customer Care</a>
      <a class="btn bg-gradient-dark w-100" href="../logout.php">Log Out</a>
    </div>
  </div>
</aside>
