<?php
session_start();
require_once '../config/config.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    // Optional: Delete physical file as well
    $stmt = $conn->prepare("SELECT file_path FROM business_ebooks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $full_path = '../' . $row['file_path'];
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM business_ebooks WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_msg = "Ebook deleted successfully!";
    } else {
        $error_msg = "Failed to delete ebook.";
    }
}

require_once '../includes/header.php';

$stmt = $conn->prepare("SELECT * FROM business_ebooks ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manage Business Ebooks</h4>
    <a href="add_ebook.php" class="btn btn-dark">Add New Ebook</a>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Reward</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created At</th>
            <th class="text-secondary opacity-7"></th>
          </tr>
        </thead>
        <tbody>
          <?php while ($ebook = $result->fetch_assoc()): ?>
            <tr>
              <td>
                <div class="d-flex px-2 py-1">
                  <div class="d-flex flex-column justify-content-center">
                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($ebook['title']) ?></h6>
                    <p class="text-xs text-secondary mb-0"><?= htmlspecialchars(substr($ebook['description'], 0, 50)) ?>...</p>
                  </div>
                </div>
              </td>
              <td>
                <p class="text-xs font-weight-bold mb-0">Ksh <?= number_format($ebook['reward_amount'], 2) ?></p>
              </td>
              <td>
                <span class="text-secondary text-xs font-weight-bold"><?= $ebook['created_at'] ?></span>
              </td>
              <td class="align-middle">
                <div class="d-flex align-items-center">
                  <a href="<?= htmlspecialchars('../' . $ebook['file_path']) ?>" class="text-secondary font-weight-bold text-xs me-3" target="_blank">
                    View
                  </a>
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ebook?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $ebook['id'] ?>">
                    <button type="submit" class="btn btn-link text-danger text-gradient px-3 mb-0"><i class="material-symbols-rounded text-sm me-2">delete</i>Delete</button>
                  </form>
                </div>
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

<?php if (isset($error_msg)): ?>
<script>
  alertify.set('notifier','position', 'top-right');
  alertify.error("<?= $error_msg ?>");
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
