<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php'; // includes sidebar + navbar

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check how many trivia questions user has already answered today
$stmt = $conn->prepare("SELECT COUNT(*) FROM trivia_attempts WHERE user_id = ? AND DATE(created_at) = CURDATE()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($answered_today);
$stmt->fetch();
$stmt->close();

$max_daily_questions = 5;
$remaining = $max_daily_questions - $answered_today;

// If they haven't reached the limit, fetch a new question they haven't answered
$question = null;
if ($remaining > 0) {
    $stmt = $conn->prepare("SELECT q.* FROM trivia_questions q
        WHERE q.id NOT IN (
            SELECT question_id FROM trivia_attempts WHERE user_id = ?
        )
        ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    $stmt->close();
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<style> input[type="radio"].form-check-input {
  accent-color: black;
}
</style>
<div class="container py-4">
    <h3 class="mb-3">🧠 Daily Trivia</h3>
    <p>You can answer up to <strong><?= $max_daily_questions ?></strong> trivia questions per day. Each correct answer earns you <strong>Ksh 20</strong>.</p>
    <p><strong><?= $answered_today ?></strong> answered today. <strong><?= $remaining ?></strong> remaining.</p>

    <?php if ($question): ?>
        <form method="POST" action="../phpscripts/submit_trivia.php">
            <div class="card p-4 mt-3">
                <h5><?= htmlspecialchars($question['question']) ?></h5>

                <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
                    <?php $opt = strtolower($option); ?>
                    <div class="form-check mt-2">
                        <input class="form-check-input accent-black" type="radio" name="answer" id="opt_<?= $option ?>" value="<?= $option ?>" required>
                        <label class="form-check-label" for="opt_<?= $option ?>">
                            <?= htmlspecialchars($question['option_' . $opt]) ?>
                        </label>
                    </div>
                <?php endforeach; ?>

                <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                <button type="submit" class="btn btn-dark mt-4">Submit Answer</button>
            </div>
        </form>
    <?php elseif ($answered_today >= $max_daily_questions): ?>
        <div class="alert alert-success mt-4">
            🎉 You've completed all your trivia questions for today. Come back tomorrow for more!
        </div>
    <?php else: ?>
        <div class="alert alert-info mt-4">
            No new trivia questions available at the moment. Please check back later.
        </div>
    <?php endif; ?>
</div>

<script>
    alertify.set('notifier','position', 'top-right');
    <?php if (isset($_SESSION['trivia_success'])): ?>
        alertify.success("<?= addslashes($_SESSION['trivia_success']) ?>");
        <?php unset($_SESSION['trivia_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['trivia_error'])): ?>
        alertify.error("<?= addslashes($_SESSION['trivia_error']) ?>");
        <?php unset($_SESSION['trivia_error']); ?>
    <?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>