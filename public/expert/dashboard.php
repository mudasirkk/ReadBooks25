<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expert Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <ul style="list-style-type: none; padding-left: 0;">
        <li style="margin-bottom: 10px;">
            📚 <a href="manage_terms.php" style="text-decoration: none; font-weight: bold; color: #007bff;">Manage Terms</a>
        </li>
        <li style="margin-bottom: 10px;">
            👁️ <a href="manage_rules.php" style="text-decoration: none; font-weight: bold; color: #007bff;">Manage If-Then Rules</a>
        </li>
    </ul>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>
</body>
</html>
