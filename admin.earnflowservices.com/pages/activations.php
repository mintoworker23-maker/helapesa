<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php';

// Fetch unactivated users
$sql = "SELECT * FROM users WHERE is_active = 0 AND (
    pay_method = 'manual' OR (pay_method = 'automatic' AND is_active = 0)
) ORDER BY created_on DESC";
$result = $conn->query($sql);
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<style>
  body {
    background-color: #ffffff;
    color: #000;
  }

  .table th, .table td {
    vertical-align: middle;
  }

  .table thead {
    background-color: #000;
    color: #fff;
  }

  .btn {
    border-radius: 20px;
    padding: 6px 14px;
  }

  .search-box {
    max-width: 300px;
    margin-bottom: 20px;
  }

  .card {
    border-radius: 16px;
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0,0,0,0.03);
  }

  .pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 5px;
  }

  .pagination button {
    border: 1px solid #ddd;
    padding: 6px 12px;
    background: #f9f9f9;
    color: #000;
    border-radius: 6px;
    cursor: pointer;
  }

  .pagination button.active {
    background: #000;
    color: #fff;
  }
</style>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Manual Activations</h4>
    <div>
      <input type="text" id="searchInput" class="form-control search-box d-inline-block border border-2 px-2" placeholder="Search username...">
    </div>
    <button class="btn btn-sm btn-outline-dark ms-2" onclick="exportTableToCSV()">Export CSV</button>
  </div>

  <?php if (count($users) > 0): ?>
    <div class="table-responsive card p-3">
      <table class="table table-bordered table-hover" id="activationTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Phone</th>
            <th>Package</th>
            <th>Amount</th>
            <th>Payment Method</th>
            <th>Transaction Code</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="userTable">
          <?php $count = 1; foreach ($users as $row): ?>
            <tr>
              <td><?= $count++ ?></td>
              <td class="username"><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= ucfirst($row['package']) ?></td>
              <td>Ksh <?= number_format($row['amount_paid']) ?></td>
              <td><?= ucfirst($row['pay_method']) ?></td>
              <td><strong><?= htmlspecialchars($row['transaction_code']) ?></strong></td>
              <td><?= date('d M Y', strtotime($row['created_on'])) ?></td>
              <td>
                <form method="POST" action="../config/process_activations.php" class="d-inline">
                  <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="package" value="<?= $row['package'] ?>">
                  <input type="hidden" name="amount" value="<?= $row['amount_paid'] ?>">
                  <button type="submit" name="approve" class="btn btn-sm btn-success" onclick="return confirm('Activate this user?')">Approve</button>
                </form>
                <form method="POST" action="../config/process_activations.php" class="d-inline">
                  <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                  <button type="submit" name="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this activation?')">Reject</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination" id="pagination"></div>
  <?php else: ?>
    <div class="alert alert-info mt-4">No pending manual or failed automatic activations.</div>
  <?php endif; ?>
</div>

<script>
// Search filter
document.getElementById('searchInput').addEventListener('keyup', function () {
  var searchValue = this.value.toLowerCase();
  var rows = document.querySelectorAll("#userTable tr");

  rows.forEach(function (row) {
    var username = row.querySelector(".username").textContent.toLowerCase();
    row.style.display = username.includes(searchValue) ? "" : "none";
  });

  updatePagination();
});

// CSV export
function exportTableToCSV() {
  var table = document.getElementById("activationTable");
  var rows = table.querySelectorAll("tr");
  var csv = [];

  rows.forEach(function (row) {
    var cols = row.querySelectorAll("th, td");
    var rowData = [];
    cols.forEach(col => {
      rowData.push(`"${col.innerText.replace(/"/g, '""')}"`);
    });
    csv.push(rowData.join(","));
  });

  var csvString = csv.join("\n");
  var blob = new Blob([csvString], { type: "text/csv" });
  var url = window.URL.createObjectURL(blob);

  var a = document.createElement("a");
  a.setAttribute("href", url);
  a.setAttribute("download", "manual_activations.csv");
  a.click();
}

// Pagination
const rowsPerPage = 20;
const table = document.getElementById("userTable");
const pagination = document.getElementById("pagination");

function paginateTable(page) {
  const rows = table.querySelectorAll("tr");
  let start = (page - 1) * rowsPerPage;
  let end = start + rowsPerPage;

  rows.forEach((row, index) => {
    row.style.display = (index >= start && index < end) ? "" : "none";
  });
}

function setupPagination() {
  const rows = table.querySelectorAll("tr");
  if (rows.length <= rowsPerPage) return;

  pagination.innerHTML = '';
  const pageCount = Math.ceil(rows.length / rowsPerPage);

  for (let i = 1; i <= pageCount; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    btn.classList.add(i === 1 ? 'active' : '');
    btn.onclick = () => {
      document.querySelectorAll(".pagination button").forEach(b => b.classList.remove('active'));
      btn.classList.add("active");
      paginateTable(i);
    };
    pagination.appendChild(btn);
  }

  paginateTable(1);
}

function updatePagination() {
  const rows = table.querySelectorAll("tr");
  const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
  const pageCount = Math.ceil(visibleRows.length / rowsPerPage);

  pagination.innerHTML = '';
  if (pageCount <= 1) return;

  for (let i = 1; i <= pageCount; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    btn.onclick = () => {
      document.querySelectorAll(".pagination button").forEach(b => b.classList.remove('active'));
      btn.classList.add("active");

      let start = (i - 1) * rowsPerPage;
      let end = start + rowsPerPage;

      visibleRows.forEach((row, index) => {
        row.style.display = (index >= start && index < end) ? "" : "none";
      });
    };
    pagination.appendChild(btn);
  }

  visibleRows.forEach((row, index) => {
    row.style.display = (index < rowsPerPage) ? "" : "none";
  });
}

document.addEventListener("DOMContentLoaded", () => {
  setupPagination();
});
</script>

<?php require_once '../includes/footer.php'; ?>