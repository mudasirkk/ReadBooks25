<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$id = intval($_GET['id'] ?? 0);
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $newRole = $_POST['role'];

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
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container">
    <h2>Edit User</h2>

    <?php foreach ($errors as $error): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endforeach; ?>

    <?php if ($message): ?>
        <p style="color:green;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" value="<?= htmlspecialchars($username); ?>" required><br><br>

        <select name="role" required>
            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
            <option value="expert" <?= $role === 'expert' ? 'selected' : '' ?>>Expert</option>
        </select><br><br>

        <button type="submit">Update User</button>
    </form>

    <p><a href="manage_users.php">← Back to User Management</a></p>
</div>
</body>
</html>
