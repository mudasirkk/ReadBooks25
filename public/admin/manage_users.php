<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$offset = ($page - 1) * $per_page;

$count_stmt = $search
    ? $conn->prepare("SELECT COUNT(*) FROM p_users WHERE username LIKE ?")
    : $conn->prepare("SELECT COUNT(*) FROM p_users");
if ($search) {
    $like = "%$search%";
    $count_stmt->bind_param("s", $like);
}
$count_stmt->execute();
$count_stmt->bind_result($total_rows);
$count_stmt->fetch();
$count_stmt->close();

$total_pages = ceil($total_rows / $per_page);

$stmt = $search
    ? $conn->prepare("SELECT id, username, role, is_protected FROM p_users WHERE username LIKE ? ORDER BY id LIMIT ? OFFSET ?")
    : $conn->prepare("SELECT id, username, role, is_protected FROM p_users ORDER BY id LIMIT ? OFFSET ?");
if ($search) {
    $stmt->bind_param("sii", $like, $per_page, $offset);
} else {
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }
        .pagination {
            display: flex;
            gap: 5px;
        }
        .pagination a {
            padding: 6px 10px;
            border: 1px solid #4CAF50;
            border-radius: 4px;
            color: #4CAF50;
            text-decoration: none;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
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
    <a href="add_user.php" class="btn-blue">➕ Add New User</a>

    <form method="GET" class="search-bar">
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
                            <a href="edit_user.php?id=<?= $row['id']; ?>&page=<?= $page ?>&per_page=<?= $per_page ?>" class="action-btn edit-btn">✏️ Edit</a>
                            <?php if (!$row['is_protected']): ?>
                                <a href="delete_user.php?id=<?= $row['id']; ?>&page=<?= $page ?>&per_page=<?= $per_page ?>" class="action-btn delete-btn" onclick="return confirm('Delete this user?');">❌ Delete</a>
                            <?php else: ?>
                                <span class="lock-icon">🔒</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <form method="GET" style="margin: 0;">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <label for="per_page">Entries per page:
                <select name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 20, 30, 50, 100] as $n): ?>
                        <option value="<?= $n ?>" <?= $n == $per_page ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&per_page=<?= $per_page ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>

</body>
</html>
