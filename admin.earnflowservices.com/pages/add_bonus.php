<?php
session_start();
require_once '../config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $amount = (float)$_POST['amount'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if ($title && $start_time && $end_time) {
        $stmt = $conn->prepare("INSERT INTO scheduled_bonuses (title, type, amount, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $title, $type, $amount, $start_time, $end_time);
        if ($stmt->execute()) {
            header("Location: bonuses.php");
            exit;
        } else {
            $error = "Failed to schedule bonus.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
require_once '../includes/header.php';
?>

<div class="container py-4">
  <h4 class="mb-3">Schedule New Bonus</h4>

  <div class="card border-0 shadow-sm p-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Bonus Title (Will appear in User Dashboard)</label>
        <input type="text" name="title" class="form-control border border-1" placeholder="e.g. Flash Bonus! Claim now" required>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Bonus Type</label>
          <select name="type" class="form-select border border-1">
            <option value="fixed_amount">Fixed Cash Amount</option>
            <option value="free_spin">Free Spin</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Value (Ksh or Number of Spins)</label>
          <input type="number" step="0.01" name="amount" class="form-control border border-1" value="50.00" required>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Start Date & Time</label>
          <input type="datetime-local" name="start_time" class="form-control border border-1" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">End Date & Time</label>
          <input type="datetime-local" name="end_time" class="form-control border border-1" required>
        </div>
      </div>
      <button type="submit" class="btn btn-dark">Schedule Bonus</button>
      <a href="bonuses.php" class="btn btn-outline-secondary ms-2">Back</a>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script>
  alertify.set('notifier','position', 'top-right');
  <?php if ($error): ?>
    alertify.error("<?= addslashes($error) ?>");
  <?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
