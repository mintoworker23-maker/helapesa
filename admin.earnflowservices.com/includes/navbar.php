<?php include('sidebar.php'); ?>

<!-- MOBILE NAVBAR -->
<nav class="navbar navbar-light bg-white d-lg-none px-3 py-2 shadow-sm border-bottom">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <!-- Left: Hamburger + Logo -->
    <div class="d-flex align-items-center justify-content-center">
      <button class="btn p-0 me-3" id="mobileSidenavToggle">
        <i class="material-symbols-rounded text-dark fs-4 mt-3">menu</i>
      </button>
      <a class="navbar-brand" href="../pages/index.php">
        <img src="../assets/images/faviconlight.png" alt="Logo" height="30">
      </a>
    </div>

    <!-- Right: Settings + Profile -->
    <div class="d-flex align-items-center">
      <a href="#" class="btn p-0 me-3 mt-2">
        <i class="material-symbols-rounded text-dark fs-4">settings</i>
      </a>
      <li class="nav-item dropdown d-flex align-items-center">
        <a href="#" class="nav-link dropdown-toggle text-body font-weight-bold px-0" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="../assets/images/default-profile.jpg" class="rounded-circle" height="32" width="32" alt="Profile">
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../logout.php">
              <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
          </li>
        </ul>
      </li>
    </div>
  </div>
</nav>

<!-- DESKTOP NAVBAR -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl d-none d-lg-flex" id="navbarBlur" data-scroll="true">
  <div class="container-fluid py-1 px-3">
    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
      <div class="ms-md-auto pe-md-3 d-flex align-items-center">
      </div>
      <ul class="navbar-nav d-flex align-items-center justify-content-end">
        <li class="nav-item px-3 d-flex align-items-center">
          <a href="../dashboard/settings.php" class="nav-link text-body p-0">
            <i class="material-symbols-rounded fixed-plugin-button-nav">settings</i>
          </a>
        </li>
        <li class="nav-item dropdown pe-3 d-flex align-items-center">
          <a href="#" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="material-symbols-rounded">notifications</i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
            <li class="mb-2">
              <a class="dropdown-item border-radius-md" href="#">
                <div class="d-flex py-1">
                  <div class="my-auto">
                    <img src="../assets/images/default-profile.jpg" class="avatar avatar-sm me-3">
                  </div>
                  <div class="d-flex flex-column justify-content-center">
                    <h6 class="text-sm font-weight-normal mb-1">
                      <span class="font-weight-bold">New message</span> from Laur
                    </h6>
                    <p class="text-xs text-secondary mb-0">
                      <i class="fa fa-clock me-1"></i>
                      13 minutes ago
                    </p>
                  </div>
                </div>
              </a>
            </li>
            <!-- Add more notifications as needed -->
          </ul>
        </li>
        <li class="nav-item dropdown d-flex align-items-center">
          <a href="#" class="nav-link dropdown-toggle text-body font-weight-bold px-0" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="../assets/images/default-profile.jpg" class="rounded-circle" height="32" width="32" alt="Profile">
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
