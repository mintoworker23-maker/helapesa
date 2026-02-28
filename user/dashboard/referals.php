<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../phpscripts/config.php';

// Check if user is logged in and activated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$referral_data = getReferralData($conn, $_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? 0;

// 1. Get unpaid and activated referrals
$stmt = $conn->prepare("
    SELECT r.id, r.bonus_amount 
    FROM referals r
    JOIN users u ON r.referred_id = u.id
    WHERE r.referrer_id = ? AND u.is_active = 1 AND r.paid = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_new_commission = 0;
$referral_ids = [];

while ($row = $result->fetch_assoc()) {
    $total_new_commission += $row['bonus_amount'];
    $referral_ids[] = $row['id'];
}
$stmt->close();

// If there's new unpaid commission
if ($total_new_commission > 0) {
    // Update user's balance and commission
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ?, commission = commission + ? WHERE id = ?");
    $stmt->bind_param("ddi", $total_new_commission, $total_new_commission, $user_id);
    $stmt->execute();
    $stmt->close();

    // Mark those referrals as paid
    $placeholders = implode(',', array_fill(0, count($referral_ids), '?'));
    $types = str_repeat('i', count($referral_ids));
    $stmt = $conn->prepare("UPDATE referals SET paid = 1 WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$referral_ids);
    $stmt->execute();
    $stmt->close();
}

include('includes/header.php');
?>

<div class="container-fluid py-4">
    <!-- Refer Friends & Earn -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Refer Friends & Earn</h5>
                    <small>Earn commissions for each successful referral</small>
                </div>
            </div>

            <div class="input-group mb-3">
                <input type="text" class="form-control border border-2 border-dark px-2" style="height:45px;" value="https://earnflowservices.com/user/register.php?ref=<?= $_SESSION['username'] ?>" id="refLink" readonly>
                <button class="btn btn-primary btn-dark" style="height:45px;" onclick="copyReferral()">Copy Link</button>
            </div>

            <!-- Social Sharing -->
            <div class="d-flex gap-3">
                <!-- Social sharing buttons -->
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Invites</h6>
                    <h3><?= count($referral_data['referred_users']) ?></h3>
                    <small>Total Invites</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Account Balance</h6>
                    <h3>Ksh <?= number_format($total_new_commission) ?></h3>
                    <small>Available Commission</small>
                </div>
            </div>
        </div>
        
        <!-- Withdrawn card -->
    </div>

    <!-- Referral Table -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Your Referrals</h5>
                <input type="text" id="searchInput" placeholder="Search...">
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="referralTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Join Date</th>
                            <th>Commission</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($referral_data['referred_users'])): ?>
                            <?php foreach ($referral_data['referred_users'] as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td><?= date('M d, Y', strtotime($user['created_on'])) ?></td>
                                    <td>Ksh <?= number_format($user['bonus_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-users fs-1 text-secondary mb-2"></i>
                                        <p class="mb-1">You haven't referred anyone yet</p>
                                        <small class="text-muted">Share your referral link to start earning</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    // JavaScript functions for copy and search
    document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#referralTable tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});
    </script>
<script>
function copyReferral() {
    const input = document.getElementById("refLink");
    const value = input.value;

    // Try Clipboard API
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(value).then(() => {
            alertify.success("Referral link copied!");
        }).catch(err => {
            fallbackCopy();
        });
    } else {
        fallbackCopy();
    }

    function fallbackCopy() {
        // Create temporary input
        const tempInput = document.createElement("input");
        tempInput.value = value;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // for iOS

        try {
            const copied = document.execCommand("copy");
            if (copied) {
                alertify.success("Referral link copied!");
            } else {
                alertify.error("Failed to copy. Please copy manually.");
            }
        } catch (err) {
            alertify.error("Copy not supported.");
        }

        document.body.removeChild(tempInput);
    }
}
</script>
    <?php include('includes/footer.php'); ?>
</div>