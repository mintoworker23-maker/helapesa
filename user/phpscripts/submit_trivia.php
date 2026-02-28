<?php
session_start();
require_once '../phpscripts/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['trivia_error'] = "You must be logged in to submit an answer.";
    header("Location: ../dashboard/trivia.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
$selected_option = isset($_POST['answer']) ? strtoupper(trim($_POST['answer'])) : '';

if (!$question_id || !in_array($selected_option, ['A', 'B', 'C', 'D'])) {
    $_SESSION['trivia_error'] = "Invalid submission.";
    header("Location: ../dashboard/trivia.php");
    exit();
}

// Check if user already answered this question
$stmt = $conn->prepare("SELECT id FROM trivia_attempts WHERE user_id = ? AND question_id = ?");
$stmt->bind_param("ii", $user_id, $question_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['trivia_error'] = "You have already answered this question.";
    header("Location: ../dashboard/trivia.php");
    exit();
}
$stmt->close();

// Get correct option and reward from trivia_questions
$stmt = $conn->prepare("SELECT correct_option, reward FROM trivia_questions WHERE id = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$stmt->bind_result($correct_option, $reward);
$stmt->fetch();
$stmt->close();

$is_correct = ($selected_option === strtoupper($correct_option));
$reward_amount = $is_correct ? (int)$reward : 0;

// Start transaction
$conn->begin_transaction();

try {
    // Insert attempt
    $stmt = $conn->prepare("INSERT INTO trivia_attempts (user_id, question_id, selected_option, is_correct, rewarded_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisii", $user_id, $question_id, $selected_option, $is_correct, $reward_amount);
    $stmt->execute();
    $stmt->close();

    // Update user balance if correct
    if ($is_correct) {
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("ii", $reward_amount, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    $_SESSION['trivia_success'] = $is_correct
        ? "✅ Correct! You've earned Ksh {$reward_amount}."
        : "❌ Incorrect. No reward for this question.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['trivia_error'] = "Something went wrong. Please try again.";
}

header("Location: ../dashboard/trivia.php");
exit();