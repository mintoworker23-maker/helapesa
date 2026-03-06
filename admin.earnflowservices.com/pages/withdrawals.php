<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../config/config.php';
require_once '../includes/header.php'; // loads sidebar & navbar

// Approve or reject logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdrawal_id = (int)$_POST['withdrawal_id'];
    $action = $_POST['action'];
    $admin_id = $_SESSION['admin_id'] ?? null; // if tracking admin approving

    if (!in_array($action, ['approve', 'reject'])) {
        $_SESSION['admin_message'] = "Invalid action.";
        header("Location: withdrawals.php");
        exit();
    }

    // Fetch withdrawal info
    $stmt = $conn->prepare("SELECT w.user_id, w.amount, w.status, w.mpesa_number, u.balance FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.id = ?");
    $stmt->bind_param("i", $withdrawal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $withdrawal = $result->fetch_assoc();
    $stmt->close();
if (headers_sent($file, $line)) {
    die("❌ Headers already sent at $file on line $line");
}
    if (!$withdrawal) {
        $_SESSION['admin_message'] = "Withdrawal not found.";
        header("Location: withdrawals.php");
        exit();
    }

    if ($withdrawal['status'] !== 'pending') {
        $_SESSION['admin_message'] = "Withdrawal already processed.";
        header("Location: withdrawals.php");
        exit();
    }

    if ($action === 'approve') {
        // Proceed to approve and deduct balance
        $conn->begin_transaction();

        try {
            if ($withdrawal['balance'] < $withdrawal['amount']) {
                throw new Exception("User has insufficient balance.");
            }

            // Deduct from user's balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param("di", $withdrawal['amount'], $withdrawal['user_id']);
            $stmt->execute();

            // Mark withdrawal as approved
            $stmt = $conn->prepare("UPDATE withdrawals SET status = 'approved', processed_at = NOW(), processed_by = ? WHERE id = ?");
            $stmt->bind_param("ii", $admin_id, $withdrawal_id);
            $stmt->execute();

            // Log in transactions
            $desc = "Withdrawal to M-Pesa ({$withdrawal['mpesa_number']})";
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'withdraw', ?)");
            $stmt->bind_param("ids", $withdrawal['user_id'], $withdrawal['amount'], $desc);
            $stmt->execute();

            $conn->commit();
            $_SESSION['admin_message'] = "✅ Withdrawal #$withdrawal_id approved.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['admin_message'] = "❌ Approval failed: " . $e->getMessage();
        }
    } else {
        // Reject only (no balance change)
        $stmt = $conn->prepare("UPDATE withdrawals SET status = 'rejected', processed_at = NOW(), processed_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $admin_id, $withdrawal_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['admin_message'] = "❌ Withdrawal #$withdrawal_id rejected.";
    }

    header("Location: withdrawals.php");
    exit();
}


// Fetch pending withdrawals
$stmt = $conn->prepare("
    SELECT w.id, w.amount, w.status, w.requested_at, w.mpesa_number, u.username 
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    ORDER BY w.requested_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Withdrawal Requests</h4>
        <input type="text" id="searchInput" class="form-control border border-2 px-2 w-auto" placeholder="Search...">
    </div>

    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['admin_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="withdrawalTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Amount (Ksh)</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows === 0): ?>
                            <tr><td colspan="7" class="text-center py-4">No withdrawal requests found.</td></tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td class="username"><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['mpesa_number']) ?></td>
                                    <td><strong>Ksh <?= number_format($row['amount']) ?></strong></td>
                                    <td><?= date('d M Y, h:i A', strtotime($row['requested_at'])) ?></td>
                                    <td><span class="badge bg-<?= $row['status'] === 'pending' ? 'warning' : ($row['status'] === 'approved' ? 'success' : 'danger') ?>">
                                        <?= ucfirst($row['status']) ?></span></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="withdrawal_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="POST" class="d-inline ms-1">
                                                <input type="hidden" name="withdrawal_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <em>No actions</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#withdrawalTable tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
