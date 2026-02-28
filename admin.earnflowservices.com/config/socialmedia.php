<?php
if ($_POST['action'] === 'approve') {
    $submission_id = $_POST['submission_id'];
    $views = intval($_POST['views']);

    // Get submission & user
    $stmt = $conn->prepare("SELECT * FROM whatsapp_submissions WHERE id = ?");
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();

    if (!$submission) {
        $_SESSION['error'] = "Submission not found.";
        header("Location: ../admin/promo_review.php?promo_id={$submission['promo_id']}");
        exit;
    }

    if ($submission['credited']) {
        $_SESSION['error'] = "This ad has already been credited.";
        header("Location: ../admin/promo_review.php?promo_id={$submission['promo_id']}");
        exit;
    }

    // Credit the user
    $user_id = $submission['user_id'];
    $reward = 150 * $views;

    // Update user balance
    $conn->query("UPDATE users SET balance = balance + $reward WHERE id = $user_id");

    // Mark submission as approved & credited
    $stmt = $conn->prepare("UPDATE whatsapp_submissions SET status = 'approved', views = ?, credited = 1 WHERE id = ?");
    $stmt->bind_param("ii", $views, $submission_id);
    $stmt->execute();

    $_SESSION['success'] = "Submission approved and user credited Ksh {$reward}.";
    header("Location: ../admin/promo_review.php?promo_id={$submission['promo_id']}");
    exit;
}
