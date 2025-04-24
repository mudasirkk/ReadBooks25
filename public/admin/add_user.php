<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8');

    if ($role === 'admin') {
        $message = "❌ You cannot create an admin user.";
    } else {
        $check = $conn->prepare("SELECT id FROM p_users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "❌ Username already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $role);
            if ($stmt->execute()) {
                $message = "✅ User created successfully!";
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
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

<header>
    <h1>Add User</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Add New User</h2>

    <?php if ($message): ?>
        <p style="<?= strpos($message, '✅') !== false ? 'color:green;' : 'color:red;' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="section">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <select name="role" required>
                <option value="user">User</option>
                <option value="expert">Expert</option>
            </select><br><br>
            <button type="submit">Create User</button>
        </div>
    </form>
    <a href="manage_users.php" style="
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    font-weight: bold;
    text-decoration: none;
    border-radius: 6px;
    transition: background-color 0.3s ease;
" onmouseover="this.style.backgroundColor='#0056b3'" onmouseout="this.style.backgroundColor='#007bff'">
    ← Back to Manage Users
</a>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>
</body>
</html>
