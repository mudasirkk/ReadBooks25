<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$result = $conn->query("SELECT id, username, role FROM p_users ORDER BY id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<div class="container">
    <h2>Manage Users</h2>
    <a href="add_user.php">Add New User</a>
    <table>
        <tr>
            <th>ID</th><th>Username</th><th>Role</th><th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['username']); ?></td>
            <td><?= htmlspecialchars($row['role']); ?></td>
            <td>
                <a href="edit_user.php?id=<?= $row['id']; ?>">Edit</a> |
                <a href="delete_user.php?id=<?= $row['id']; ?>" onclick="return confirm('Delete this user?');">❌ Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
