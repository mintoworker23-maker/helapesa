<?php
session_start();
require_once '../config/config.php'; // DB config
include '../includes/header.php';

// Ensure admin-only access (you can adjust this logic)
//if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    //header("Location: index.php");//
    //exit();//
//}

// Fetch users
$search = $_GET['search'] ?? '';
$query = "SELECT id, username, email, phone, profile_picture, created_on FROM users WHERE username LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_on DESC";
$stmt = $conn->prepare($query);
$param = "%$search%";
$stmt->bind_param("sss", $param, $param, $param);
$stmt->execute();
$result = $stmt->get_result();
?>

<main class="main-content position-relative border-radius-lg">
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">All Users</h4>
      <form method="get" class="d-flex" role="search">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2 w-100 h-100 border border-1 px-1" placeholder="Search username or email">
        <button class="btn btn-primary w-100 bg-dark" type="submit">Search</button>
      </form>
    </div>

    <div class="card">
      <div class="card-header pb-0">
        <h6>User List</h6>
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive">
          <table class="table table-bordered align-items-center mb-0 w-100">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Profile</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2">Username</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2">Email</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2">Phone</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2">Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($user = $result->fetch_assoc()): ?>
              <tr>
                <td>
                  <div class="d-flex px-2 py-1">
                    <div>
                      <img src="<?= $user['profile_picture'] ?: '../assets/images/default-profile.jpg' ?>" class="avatar avatar-sm me-3" alt="user">
                    </div>
                  </div>
                </td>
                <td>
                  <a href="user_detail.php?id=<?= $user['id'] ?>" class="text-sm mb-0 text-primary text-decoration-none" id="usernameLink">
                    <?= htmlspecialchars($user['username']) ?>
                  </a>
                </td>
                <td>
                  <p class="text-sm mb-0"><?= htmlspecialchars($user['phone']) ?></p>
                </td>
                <td>
                  <p class="text-sm mb-0"><?= htmlspecialchars($user['email']) ?></p>
                </td>
                <td>
                  <p class="text-sm mb-0"><?= date('d M Y', strtotime($user['created_on'])) ?></p>
                </td>
              </tr>
              <?php endwhile; ?>
              <?php if ($result->num_rows === 0): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
