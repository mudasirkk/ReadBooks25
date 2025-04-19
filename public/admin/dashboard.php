<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expert Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard-btn {
            display: block; 
            background-color: #007bff;
            color: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            font-size: 16px;
            margin-bottom: 15px;
            width: 250px;
        }

        .dashboard-btn:hover {
            background-color: #0056b3;
        }

        .dashboard-section {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>

    <div class="dashboard-section">
        <a href="manage_users.php" class="dashboard-btn">👤 Manage Users</a>
        <a href="../expert/manage_terms.php" class="dashboard-btn">📚 Manage Terms</a>
        <a href="../expert/manage_rules.php" class="dashboard-btn">🔗 Manage If-Then Rules</a>
    </div>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>
</body>
</html>
