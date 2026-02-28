<?php
ob_start();
session_start();
require_once '../config/config.php';

$success = '';
$error = '';

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct = $_POST['correct'] ?? '';

    if ($question && $option_a && $option_b && $option_c && $option_d && in_array($correct, ['A', 'B', 'C', 'D'])) {
        $stmt = $conn->prepare("INSERT INTO trivia_questions (question, option_a, option_b, option_c, option_d, correct_answer, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $question, $option_a, $option_b, $option_c, $option_d, $correct);
        if ($stmt->execute()) {
            header("Location: trivia_list.php");
            exit();
        } else {
            $error = "Failed to save question: " . $stmt->error;
        }
    } else {
        $error = "Please fill all fields and choose a correct answer.";
    }
}
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<div class="container py-4">
  <h4 class="mb-3">Add Trivia Question</h4>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Question</label>
      <textarea name="question" class="form-control border border-2" rows="2" required></textarea>
    </div>

    <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
      <?php $opt = strtolower($option); ?>
      <div class="mb-3">
        <label class="form-label">Option <?= $option ?></label>
        <input type="text" name="option_<?= $opt ?>" class="form-control border border-2" required>
      </div>
    <?php endforeach; ?>

    <div class="mb-3">
      <label class="form-label">Correct Answer</label><br>
      <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="correct" id="correct_<?= $option ?>" value="<?= $option ?>" required>
          <label class="form-check-label" for="correct_<?= $option ?>"><?= $option ?></label>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn btn-dark">Save Question</button>
    <a href="trivia_list.php" class="btn btn-outline-secondary ms-2">Back to Questions</a>
  </form>
</div>

<script>
  alertify.set('notifier','position', 'top-right');
  <?php if ($success): ?>
    alertify.success("<?= addslashes($success) ?>");
  <?php endif; ?>
  <?php if ($error): ?>
    alertify.error("<?= addslashes($error) ?>");
  <?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
