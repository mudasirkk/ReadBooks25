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
        .dashboard-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
        }

        .dashboard-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.2s ease-in-out;
            min-width: 250px;
            text-align: center;
        }

        .dashboard-button:hover {
            background-color: #0056b3;
        }

        .dashboard-button span {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<header>
    <h1>Expert Dashboard</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container" style="text-align: center;">
    <h2 class="section-title">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>

    <div class="dashboard-buttons">
        <a href="manage_terms.php" class="dashboard-button">
            <span>📚</span> Manage Terms
        </a>
        <a href="manage_rules.php" class="dashboard-button">
            <span>🔗</span> Manage If-Then Rules
        </a>
    </div>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>
</body>
</html>
