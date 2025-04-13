<?php
require_once '../includes/auth.php';
require_role('admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome, Admin <?php echo $_SESSION['username']; ?>!</h2>

    <ul>
        <li><a href="../register.php">Add New User</a></li>
        <li><a href="../expert/dashboard.php">Manage Knowledge Base (Expert Mode)</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</body>
</html>
