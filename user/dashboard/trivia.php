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

<style>
    .trivia-option {
        display: block;
        padding: 15px 20px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .trivia-option:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .trivia-input {
        display: none;
    }

    .trivia-input:checked + .trivia-option {
        border-color: #1A73E8;
        background-color: rgba(26, 115, 232, 0.05);
    }

    .trivia-input:checked + .trivia-option::after {
        content: '\e876';
        font-family: 'Material Symbols Rounded';
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #1A73E8;
        font-weight: bold;
    }

    .progress-compact {
        height: 8px;
        border-radius: 4px;
        background-color: #e9ecef;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="font-weight-bolder mb-0">🧠 Daily Trivia</h3>
                    <p class="text-muted small mb-0">Test your knowledge and earn rewards</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-gradient-success">Ksh 20 per correct answer</span>
                </div>
            </div>

            <div class="card feature-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-xs font-weight-bold text-uppercase">Daily Progress</span>
                        <span class="text-xs font-weight-bold"><?= $answered_today ?> / <?= $max_daily_questions ?> Answered</span>
                    </div>
                    <div class="progress progress-compact mb-3">
                        <?php $progress = ($answered_today / $max_daily_questions) * 100; ?>
                        <div class="progress-bar bg-gradient-dark" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="text-sm text-muted mb-0">
                        You have <strong><?= $remaining ?></strong> questions remaining for today.
                    </p>
                </div>
            </div>

            <?php if ($question): ?>
                <form method="POST" action="../phpscripts/submit_trivia.php" id="triviaForm">
                    <div class="card feature-card p-4">
                        <h5 class="font-weight-bold mb-4"><?= htmlspecialchars($question['question']) ?></h5>

                        <div class="trivia-options-container">
                            <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
                                <?php $opt = strtolower($option); ?>
                                <label class="w-100">
                                    <input class="trivia-input" type="radio" name="answer" value="<?= $option ?>" required>
                                    <div class="trivia-option">
                                        <span class="font-weight-bold me-2"><?= $option ?>.</span>
                                        <?= htmlspecialchars($question['option_' . $opt]) ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                        <button type="submit" class="btn btn-dark btn-custom w-100 mt-3">
                            SUBMIT MY ANSWER
                        </button>
                    </div>
                </form>
            <?php elseif ($answered_today >= $max_daily_questions): ?>
                <div class="card feature-card border-0 bg-gradient-success p-4 text-center">
                    <div class="mb-3">
                        <span class="material-symbols-rounded text-white display-4">celebration</span>
                    </div>
                    <h4 class="text-white font-weight-bold">Daily Goal Reached!</h4>
                    <p class="text-white opacity-8">🎉 You've completed all your trivia questions for today. Come back tomorrow for more challenges!</p>
                </div>
            <?php else: ?>
                <div class="card feature-card p-5 text-center">
                    <div class="mb-3 text-muted">
                        <span class="material-symbols-rounded display-4">quiz</span>
                    </div>
                    <h4 class="font-weight-bold">No Questions Available</h4>
                    <p class="text-muted">No new trivia questions available at the moment. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
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

    document.getElementById('triviaForm')?.addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
    });
</script>

<?php require_once 'includes/footer.php'; ?>