<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0;
$message = '';
$errors = [];

$check_admin = $conn->prepare("SELECT username FROM p_users WHERE id = ?");
$check_admin->bind_param("i", $id);
$check_admin->execute();
$check_admin->bind_result($target_username);
$check_admin->fetch();
$check_admin->close();

if ($target_username === 'admin1') {
    echo "<h3 style='text-align: center; margin-top: 50px;'>❌ Editing the protected admin account is not allowed.</h3>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $newRole = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    if (!in_array($newRole, ['user', 'expert'])) {
        $errors[] = "❌ Invalid role. Only 'user' or 'expert' allowed.";
    }

    if (empty($newUsername)) {
        $errors[] = "❌ Username cannot be empty.";
    } else {
        $check = $conn->prepare("SELECT id FROM p_users WHERE username = ? AND id != ?");
        $check->bind_param("si", $newUsername, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errors[] = "❌ Username already exists.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE p_users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newUsername, $newRole, $id);
        if ($stmt->execute()) {
            $message = "✅ User updated successfully.";
        } else {
            $errors[] = "❌ Failed to update user.";
        }
    }
}

$stmt = $conn->prepare("SELECT username, role FROM p_users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($username, $role);
$stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<header>
    <h1>Edit User</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Edit User</h2>

    <?php foreach ($errors as $error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required><br><br>
        <select name="role" required>
            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
            <option value="expert" <?= $role === 'expert' ? 'selected' : '' ?>>Expert</option>
        </select><br><br>
        <button type="submit">Update User</button>
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
