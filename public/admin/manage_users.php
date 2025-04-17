<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>Manage Users</h2>
    <a href="add_user.php">➕ Add New User</a>

    <table>
        <thead>
            <tr>
                <th style="background-color: #4CAF50; color: white;">ID</th>
                <th style="background-color: #4CAF50; color: white;">Username</th>
                <th style="background-color: #4CAF50; color: white;">Role</th>
                <th style="background-color: #4CAF50; color: white;">Protected</th>
                <th style="background-color: #4CAF50; color: white;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT id, username, role, is_protected FROM p_users ORDER BY id");
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td><?= htmlspecialchars($row['role']); ?></td>
                    <td><?= $row['is_protected'] ? '✅' : '❌'; ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $row['id']; ?>">✏️ Edit</a>
                        <?php if (!$row['is_protected']): ?>
                            | <a href="delete_user.php?id=<?= $row['id']; ?>" onclick="return confirm('Delete this user?');">❌ Delete</a>
                        <?php else: ?>
                            | <span style="color: gray;">🔒</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>

</body>
</html>
