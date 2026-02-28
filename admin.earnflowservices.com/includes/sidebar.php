<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
$isVideoPage = in_array($currentPage, ['youtube_videos.php', 'add_video.php']);
$isTriviaPage = in_array($currentPage, ['trivia_list.php', 'trivias.php']);
$isSocialPage = in_array($currentPage, ['socialmediaads.php', 'reviewads.php', 'add_whatsapp.php']);
$isUsersPage = in_array($currentPage, ['users.php', 'user_detail.php']);
$isAdsPage = in_array($currentPage, ['adverts.php', 'add_ad.php']);
$package = strtolower(trim($_SESSION['package'] ?? 'basic'));
?>

<!-- Remove d-none class from aside element -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
  <div class="sidenav-header">
    <a class="navbar-brand px-4 py-3 m-0" href="index.php">
      <img src="../assets/images/faviconlight.png" class="navbar-brand-img" alt="main_logo" height="30">
    </a>
  </div>

  <hr class="horizontal dark mt-0 mb-2">

  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'index.php' ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="index.php">
          <i class="material-symbols-rounded opacity-5">dashboard</i>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'withdrawals.php' ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="../pages/withdrawals.php">
          <i class="material-symbols-rounded opacity-5">account_balance_wallet</i>
          <span class="nav-link-text ms-1">Withdrawals</span>
        </a>
      </li>

      <!--?php if (in_array($package, ['silver', 'gold', 'premium'])):?-->
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'activations.php' ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="../pages/activations.php">
          <i class="material-symbols-rounded opacity-5">stadia_controller</i>
          <span class="nav-link-text ms-1">Activations</span>
        </a>
      </li>
      <!--?php endif;?-->

      <li class="nav-item">
        <a class="nav-link <?= $isTriviaPage ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="../pages/trivia_list.php">
          <i class="material-symbols-rounded opacity-5">groups</i>
          <span class="nav-link-text ms-1">Trivias</span>
        </a>
      </li>

      <!--?php if (in_array($package, ['gold', 'premium'])):?-->
      <li class="nav-item">
        <a class="nav-link <?= $isVideoPage ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="../pages/youtube_videos.php">
          <i class="material-symbols-rounded opacity-5">crossword</i>
          <span class="nav-link-text ms-1">Youtube Videos</span>
        </a>
      </li>
      <!--?php endif;?-->

      <!--?php if ($package === 'premium'):?-->
      <li class="nav-item">
        <a class="nav-link <?= $isSocialPage ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="../pages/socialmediaads.php">
          <i class="material-symbols-rounded opacity-5">campaign</i>
          <span class="nav-link-text ms-1">Whatsapp Ads</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $isAdsPage ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="../pages/adverts.php">
          <i class="material-symbols-rounded opacity-5">youtube_activity</i>
          <span class="nav-link-text ms-1">adverts</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $isUsersPage ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="users.php">
          <i class="material-symbols-rounded opacity-5">mood</i>
          <span class="nav-link-text ms-1">Users</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link <?= $currentPage == 'settings.php' ? 'bg-gradient-dark text-white' : 'text-dark' ?>" href="settings.php">
          <i class="material-symbols-rounded opacity-5">settings</i>
          <span class="nav-link-text ms-1">Settings</span>
        </a>
      </li>
      <!--?php endif;?-->
    </ul>
  </div>

  <div class="sidenav-footer position-absolute w-100 bottom-0">
    <div class="mx-3">
      <a class="btn btn-outline-dark mt-4 w-100" href="https://dashboard.tawk.to/#/chat">Chat</a>
      <a class="btn bg-gradient-dark w-100" href="../logout.php">Log Out</a>
    </div>
  </div>
</aside>