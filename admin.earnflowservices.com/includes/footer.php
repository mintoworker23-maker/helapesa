
<footer class="footer py-4  ">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script> <?= getSiteSetting($conn, 'site_name') ? htmlspecialchars(getSiteSetting($conn, 'site_name')) : 'Helapesa' ?>,
                Website by
                <a href="http://tcmedia.online" class="font-weight-bold" target="_blank">TCMedia</a>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </main>


<script src="../assets/js/core/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Toastify Initialization for PHP Flash Messages
  <?php if(isset($message) && !empty($message)): ?>
    Toastify({
        text: "<?= addslashes($message) ?>",
        duration: 3000,
        close: true,
        gravity: "top", 
        position: "right", 
        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
    }).showToast();
  <?php endif; ?>

  <?php if(isset($success) && !empty($success)): ?>
    Toastify({
        text: "<?= addslashes($success) ?>",
        duration: 3000,
        close: true,
        gravity: "top", 
        position: "right", 
        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
    }).showToast();
  <?php endif; ?>

  <?php if(isset($error) && !empty($error)): ?>
    Toastify({
        text: "<?= addslashes($error) ?>",
        duration: 3000,
        close: true,
        gravity: "top", 
        position: "right", 
        backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
    }).showToast();
  <?php endif; ?>

  // Session Flash Messages
  <?php if(isset($_SESSION['message']) && !empty($_SESSION['message'])): ?>
    Toastify({
        text: "<?= addslashes($_SESSION['message']) ?>",
        duration: 3000,
        close: true,
        gravity: "top", 
        position: "right", 
        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
    }).showToast();
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>
  
  <?php if(isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
    Toastify({
        text: "<?= addslashes($_SESSION['success']) ?>",
        duration: 3000,
        close: true,
        gravity: "top", 
        position: "right", 
        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
    }).showToast();
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
    Toastify({
        text: "<?= addslashes($_SESSION['error']) ?>",
        duration: 3000,
        close: true,
        gravity: "top", 
        position: "right", 
        backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
    }).showToast();
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>


  const toggleBtn = document.getElementById('mobileSidenavToggle');
  const sidenav = document.getElementById('sidenav-main');
  const overlay = document.getElementById('overlay');
  const body = document.body;

  // Mobile toggle button click
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      sidenav.classList.toggle('sidebar-open');
      overlay.classList.toggle('d-none');
      body.classList.toggle('overflow-hidden');
    });
  }

  // Overlay click to close sidebar
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidenav.classList.remove('sidebar-open');
      overlay.classList.add('d-none');
      body.classList.remove('overflow-hidden');
    });
  }

  // Escape key to close sidebar
  document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !overlay.classList.contains('d-none')) {
      sidenav.classList.remove('sidebar-open');
      overlay.classList.add('d-none');
      body.classList.remove('overflow-hidden');
    }
  });
});
</script>
<div id="overlay" 
     class="position-fixed top-0 start-0 w-100 h-100 d-none" 
     style="z-index: 9998; background: rgba(0,0,0,0.3); transition: opacity 0.3s ease;"></div>
    </body>

</html>