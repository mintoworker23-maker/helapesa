<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $message = trim($_POST['message']);
            if ($message) {
                $stmt = $conn->prepare("INSERT INTO notifications (message) VALUES (?)");
                $stmt->bind_param("s", $message);
                $stmt->execute();
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } elseif ($_POST['action'] === 'toggle') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE notifications SET is_active = !is_active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
}

$result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");
require_once '../includes/header.php';
?>

<div class="container py-4">
  <h4 class="mb-3">Dashboard Notifications</h4>

  <div class="card border-0 shadow-sm p-4 mb-4">
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="mb-3">
        <label class="form-label">New Notification Message</label>
        <textarea name="message" class="form-control border border-1" rows="3" placeholder="Enter message to display on user dashboard..." required></textarea>
      </div>
      <button type="submit" class="btn btn-dark">Post Notification</button>
    </form>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Message</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created</th>
            <th class="text-secondary opacity-7"></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($notif = $result->fetch_assoc()): ?>
            <tr>
              <td><p class="text-sm px-3 mb-0"><?= htmlspecialchars($notif['message']) ?></p></td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= $notif['id'] ?>">
                  <button type="submit" class="btn btn-sm <?= $notif['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                    <?= $notif['is_active'] ? 'Active' : 'Inactive' ?>
                  </button>
                </form>
              </td>
              <td><span class="text-secondary text-xs font-weight-bold"><?= $notif['created_at'] ?></span></td>
              <td class="align-middle">
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $notif['id'] ?>">
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

<?php require_once '../includes/footer.php'; ?>
