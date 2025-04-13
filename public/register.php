<?php
require_once '../includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username && $password && $role) {
        $valid_roles = ['admin', 'expert', 'user'];

        if (!in_array($role, $valid_roles)) {
            $error = "Invalid role selected.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                $success = "User registered successfully!";
            } else {
                $error = "Username already exists or registration failed.";
            }
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Create an Account</h2>

    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="user">User</option>
            <option value="expert">Expert</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Already have an account? Log in</a></p>
</body>
</html>
