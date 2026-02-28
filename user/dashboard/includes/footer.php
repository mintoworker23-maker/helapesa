
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
  <script src="../assets/js/material-dashboard.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
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


<script>
document.addEventListener("DOMContentLoaded", function () {
  const toggles = [document.getElementById('themeToggle'), document.getElementById('themeToggleMobile')];
  const icons = [document.getElementById('themeIcon'), document.getElementById('themeIconMobile')];
  const root = document.body;

  const savedTheme = localStorage.getItem("theme") || "light";
  root.setAttribute("data-theme", savedTheme);
  icons.forEach(icon => {
    if (icon) icon.textContent = savedTheme === "dark" ? "light_mode" : "dark_mode";
  });

  toggles.forEach((btn, i) => {
    if (btn) {
      btn.addEventListener("click", () => {
        const isDark = root.getAttribute("data-theme") === "dark";
        const newTheme = isDark ? "light" : "dark";
        root.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);
        icons.forEach(icon => {
          if (icon) icon.textContent = newTheme === "dark" ? "light_mode" : "dark_mode";
        });
      });
    }
  });
});
</script>
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/685d6cfa767d56190e241744/1iumf3cru';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!--End of Tawk.to Script-->
    <!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="../phpscripts/depositconfig.php" id="depositForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="depositModalLabel">Deposit Funds</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
          <label for="depositPhone" class="form-label">Phone Number</label>
          <input type="text" readonly class="form-control border border-2 border-dark text-center px-2" value="<?= htmlspecialchars($mpesa_number ?? '') ?>">
        </div>

        <div class="mb-3">
          <label for="depositAmount" class="form-label">Amount (Min: Ksh 50)</label>
          <input type="number" min="50" class="form-control border border-2 border-dark text-center px-2" id="depositAmount" name="deposit_amount" placeholder="e.g. 100">
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-dark w-100">Deposit</button>
      </div>
    </form>
  </div>
</div>
<script>
const formData = new FormData();
formData.append("deposit_amount", 100);

fetch("/user/phpscripts/depositconfig.php", {
  method: "POST",
  body: formData,
})
  .then((res) => res.json())
  .then((data) => {
    if (data.success) {
      alert("STK push sent to phone.");
    } else {
      alert("Error: " + data.message);
    }
  })
  .catch((err) => {
    console.error("Error:", err);
  });
</script>

    </body>

</html>