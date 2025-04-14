<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if ($role === 'admin') {
        $message = "You cannot create an admin user.";
    } else {
        $stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);
        if ($stmt->execute()) {
            $message = "User created successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
    <h2>Add New User</h2>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role" required>
            <option value="user">User</option>
            <option value="expert">Expert</option>
        </select><br><br>
        <button type="submit">Create User</button>
    </form>
</div>
</body>
</html>
