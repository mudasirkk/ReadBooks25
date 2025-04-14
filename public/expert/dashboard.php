<?php
require_once '../includes/auth.php';
require_role('expert');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expert Dashboard</title>
</head>
<body>
    <h2>Welcome, Expert <?php echo $_SESSION['username']; ?>!</h2>

    <ul>
        <li><a href="manage_terms.php">Manage Terms</a></li>
        <li><a href="manage_rules.php">Manage If-Then Rules</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</body>
</html>
