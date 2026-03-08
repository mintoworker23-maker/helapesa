<?php
session_start();
require_once '../config/config.php';

// Handle Welcome Bonus Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_welcome') {
    $amount = (float)$_POST['welcome_bonus'];
    $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('welcome_bonus', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->bind_param("s", $amount);
    $stmt->execute();
    $success_msg = "Welcome bonus updated!";
}

// Update status of bonuses based on current time
$conn->query("UPDATE scheduled_bonuses SET status = 'active' WHERE status = 'scheduled' AND start_time <= NOW() AND end_time > NOW()");
$conn->query("UPDATE scheduled_bonuses SET status = 'expired' WHERE status = 'active' AND end_time <= NOW()");

$stmt = $conn->prepare("SELECT * FROM scheduled_bonuses ORDER BY start_time DESC");
$stmt->execute();
$result = $stmt->get_result();

$welcome_bonus = getSiteSetting($conn, 'welcome_bonus') ?: '0';

require_once '../includes/header.php';
?>

<div class="container py-4">
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-4">
        <h5 class="font-weight-bold mb-3">Welcome Bonus</h5>
        <form method="POST">
          <input type="hidden" name="action" value="update_welcome">
          <div class="mb-3">
            <label class="form-label">Reward for New Users (Ksh)</label>
            <input type="number" step="0.01" name="welcome_bonus" class="form-control border border-1" value="<?= htmlspecialchars($welcome_bonus) ?>">
          </div>
          <button type="submit" class="btn btn-dark mb-0">Update Welcome Bonus</button>
        </form>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manage Scheduled Bonuses</h4>
    <a href="add_bonus.php" class="btn btn-dark">Schedule New Bonus</a>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Value</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Starts</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ends</th>
            <th class="text-secondary opacity-7"></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($bonus = $result->fetch_assoc()): ?>
            <tr>
              <td><h6 class="mb-0 text-sm px-3"><?= htmlspecialchars($bonus['title']) ?></h6></td>
              <td><p class="text-xs font-weight-bold mb-0"><?= str_replace('_', ' ', ucfirst($bonus['type'])) ?></p></td>
              <td><p class="text-xs font-weight-bold mb-0"><?= $bonus['type'] == 'free_spin' ? (int)$bonus['amount'] . ' Spins' : 'Ksh ' . number_format($bonus['amount'], 2) ?></p></td>
              <td>
                <span class="badge badge-sm <?= $bonus['status'] == 'active' ? 'bg-gradient-success' : ($bonus['status'] == 'scheduled' ? 'bg-gradient-info' : 'bg-gradient-secondary') ?>">
                  <?= ucfirst($bonus['status']) ?>
                </span>
              </td>
              <td><span class="text-secondary text-xs font-weight-bold"><?= $bonus['start_time'] ?></span></td>
              <td><span class="text-secondary text-xs font-weight-bold"><?= $bonus['end_time'] ?></span></td>
              <td class="align-middle">
                <form method="POST" action="delete_bonus.php" onsubmit="return confirm('Are you sure?')">
                  <input type="hidden" name="id" value="<?= $bonus['id'] ?>">
                  <button type="submit" class="btn btn-link text-danger text-gradient px-3 mb-0"><i class="material-symbols-rounded text-sm me-2">delete</i>Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (isset($success_msg)): ?>
<script>
  alertify.set('notifier','position', 'top-right');
  alertify.success("<?= $success_msg ?>");
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
