<?php
$ref = isset($_GET['ref']) ? '?ref=' . urlencode($_GET['ref']) : '';
header("Location: login.php" . $ref);
exit();
