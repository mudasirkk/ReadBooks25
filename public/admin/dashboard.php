<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tools - Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .admin-links {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        .admin-links a {
            display: block;
            margin: 10px 0;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .admin-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Tools</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <div class="admin-links">
        <a href="manage_users.php">👤 Manage Users</a>
        <a href="../expert/manage_terms.php">📚 Manage Terms</a>
        <a href="../expert/manage_rules.php">🔗 Manage If-Then Rules</a>
    </div>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>

</body>
</html>
