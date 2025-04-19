<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$search = $_GET['search'] ?? '';
$search_sql = $search ? "WHERE username LIKE ?" : "ORDER BY id";
$stmt = $search
    ? $conn->prepare("SELECT id, username, role, is_protected FROM p_users WHERE username LIKE ? ORDER BY id")
    : $conn->prepare("SELECT id, username, role, is_protected FROM p_users ORDER BY id");

if ($search) {
    $like = "%$search%";
    $stmt->bind_param("s", $like);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .btn-blue {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn-blue:hover {
            background-color: #0056b3;
        }

        .action-btn {
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
        }

        .edit-btn {
            background-color: #007bff;
            color: #fff;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .action-pair {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lock-icon {
            color: gray;
            font-size: 20px;
            margin-left: 8px;
        }

        .search-bar {
            margin-bottom: 15px;
        }

        .search-bar input[type="text"] {
            padding: 6px;
            width: 220px;
        }

        .search-bar button {
            padding: 6px 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <h1>User Management Dashboard</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>Manage Users</h2>
    <hr style="border-top: 2px solid black; margin-top: 20px; margin-bottom: 20px;">
    <br>
    <a href="add_user.php" class="btn-blue">➕ Add New User</a>

    <form method="GET" class="search-bar">
    <hr style="border-top: 2px solid black; margin-top: 20px; margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Search username..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

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
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td><?= htmlspecialchars($row['role']); ?></td>
                    <td><?= $row['is_protected'] ? '✅' : '❌'; ?></td>
                    <td>
                        <div class="action-pair">
                            <a href="edit_user.php?id=<?= $row['id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                            <?php if (!$row['is_protected']): ?>
                                <a href="delete_user.php?id=<?= $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this user?');">❌ Delete</a>
                            <?php else: ?>
                                <span class="lock-icon">🔒</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>

</body>
</html>
