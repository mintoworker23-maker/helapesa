<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php';

// Fetch all trivia questions
$stmt = $conn->prepare("SELECT * FROM trivia_questions ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
.trivia-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.trivia-card {
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px;
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.correct-option {
  font-weight: bold;
  color: green;
}

.add-trivia-card {
  border: 2px dashed #aaa;
  text-align: center;
  font-size: 30px;
  color: #aaa;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 150px;
  border-radius: 12px;
  transition: background 0.2s ease;
}

.add-trivia-card:hover {
  background: #f9f9f9;
  color: #555;
}
</style>

<div class="container py-4">
  <h4 class="mb-3">Manage Trivia Questions</h4>

  <div class="trivia-grid">
    <!-- Add New Block -->
    <a href="trivias.php" class="add-trivia-card">
      <div>+</div>
    </a>

    <!-- Trivia Cards -->
    <?php while ($q = $result->fetch_assoc()): ?>
    <div class="trivia-card">
        <h6><?= htmlspecialchars($q['question']) ?></h6>
        <ul class="mt-3 mb-2 list-unstyled">
        <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
            <?php 
            $key = 'option_' . strtolower($option); 
            $isCorrect = ($q['correct_answer'] == $option);
            ?>
            <li class="<?= $isCorrect ? 'correct-option' : '' ?>">
            <strong><?= $option ?>.</strong> <?= htmlspecialchars($q[$key]) ?>
            </li>
        <?php endforeach; ?>
        </ul>
        <small class="text-muted">Created: <?= date('d M Y', strtotime($q['created_at'])) ?></small>
    </div>
    <?php endwhile; ?>
  </div>
</div>
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
  <script>
    alertify.set('notifier','position', 'top-right');
    alertify.success('Trivia question added successfully!');
  </script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
