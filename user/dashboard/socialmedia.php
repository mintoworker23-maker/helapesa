<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php'; // navbar + sidebar

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch active promo
$stmt = $conn->prepare("SELECT * FROM whatsapp_promos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$promo = $result->fetch_assoc();
$stmt->close();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<div class="container py-4">
    <h3 class="mb-3">Earn by Promoting on WhatsApp</h3>

    <?php if ($promo): ?>
        <div class="card p-4 mb-4">
            <h5 class="mb-3"><?= htmlspecialchars($promo['title']) ?></h5>
            <p class="mb-2">Download this image and post it on your WhatsApp Status for 24 hours.</p>
            <div class="mb-3 text-center">
                <img src="<?= $promo['image_path'] ?>" alt="Promo Image" class="img-fluid border" style="max-height: 300px;">
            </div>
            <div class="text-center mb-4">
                <a href="../<?= $promo['image_path'] ?>" download class="btn btn-dark">Download Image</a>
            </div>
            <hr>
            <form method="POST" action="../phpscripts/submit_whatsapp_proof.php" enctype="multipart/form-data">
                <input type="hidden" name="promo_id" value="<?= $promo['id'] ?>">
                <div class="form-group mb-3">
                    <label for="screenshot">Upload screenshot showing your WhatsApp status + views:</label>
                    <input type="file" name="screenshot" id="screenshot" class="form-control border border-dark" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-success btn-dark">Submit for Review</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No active WhatsApp promotions at the moment. Please check back later.
        </div>
    <?php endif; ?>
</div>

<!-- User Submissions -->
<?php
$submissions = $conn->prepare("SELECT * FROM whatsapp_submissions WHERE user_id = ? ORDER BY created_at DESC");
$submissions->bind_param("i", $user_id);
$submissions->execute();
$subResults = $submissions->get_result();
?>

<?php if ($subResults->num_rows > 0): ?>
    <h5 class="mt-5">Your Submissions</h5>
    <div class="table-responsive">
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Promo</th>
                    <th>Submitted On</th>
                    <th>Status</th>
                    <th>Screenshot</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $subResults->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($row['promo_id']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                        <td>
                            <?php
                                if ($row['status'] == 'pending') echo '<span class="badge bg-warning">Pending</span>';
                                elseif ($row['status'] == 'approved') echo '<span class="badge bg-success">Approved</span>';
                                else echo '<span class="badge bg-danger">Rejected</span>';
                            ?>
                        </td>
                        <td><a href="../<?= $row['screenshot_path'] ?>" target="_blank">View</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<?php if (isset($_SESSION['whatsapp_success'])): ?>
<script>alertify.success("<?= addslashes($_SESSION['whatsapp_success']) ?>");</script>
<?php unset($_SESSION['whatsapp_success']); endif; ?>

<?php if (isset($_SESSION['whatsapp_error'])): ?>
<script>alertify.error("<?= addslashes($_SESSION['whatsapp_error']) ?>");</script>
<?php unset($_SESSION['whatsapp_error']); endif; ?>

<?php require_once 'includes/footer.php'; ?>