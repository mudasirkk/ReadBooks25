<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
require_once '../includes/db.php';

$success = '';
$error = '';
$edit_id = '';
$edit_cond = '';
$edit_result = '';
$search = $_GET['search'] ?? '';
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
$offset = ($current_page - 1) * $per_page;

// Fetch total rows for pagination
$count_stmt = $search
    ? $conn->prepare("SELECT COUNT(*) FROM conditional_knowledge WHERE condition_if LIKE ? OR consequence_then LIKE ?")
    : $conn->prepare("SELECT COUNT(*) FROM conditional_knowledge");
if ($search) {
    $like = "%$search%";
    $count_stmt->bind_param("ss", $like, $like);
}
$count_stmt->execute();
$count_stmt->bind_result($total_rows);
$count_stmt->fetch();
$count_stmt->close();
$total_pages = max(1, ceil($total_rows / $per_page));

// If adding a new term, go to last page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['id'])) {
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $per_page;
}

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT condition_if, consequence_then FROM conditional_knowledge WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_cond, $edit_result);
    $stmt->fetch();
    $stmt->close();
}

if (isset($_GET['updated'])) $success = "✅ Rule updated.";
elseif (isset($_GET['added'])) $success = "✅ Rule added!";
elseif (isset($_GET['deleted'])) $success = "✅ Rule deleted.";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $condition = trim($_POST['condition']);
    $result_text = trim($_POST['result']);
    $rule_id = $_POST['id'] ?? null;

    if ($condition && $result_text) {
        if (!$rule_id) {
            $check = $conn->prepare("SELECT id FROM conditional_knowledge WHERE condition_if = ? AND consequence_then = ?");
            $check->bind_param("ss", $condition, $result_text);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) $error = "❌ This rule already exists.";
            $check->close();
        }

        if (!$error) {
            if ($rule_id) {
                $stmt = $conn->prepare("UPDATE conditional_knowledge SET condition_if=?, consequence_then=? WHERE id=?");
                $stmt->bind_param("ssi", $condition, $result_text, $rule_id);
                if ($stmt->execute()) {
                    header("Location: manage_rules.php?updated=1&page=$current_page&per_page=$per_page");
                    exit;
                } else $error = "❌ Update failed.";
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO conditional_knowledge (condition_if, consequence_then) VALUES (?, ?)");
                $stmt->bind_param("ss", $condition, $result_text);
                if ($stmt->execute()) {
                    $new_total = $total_rows + 1;
                    $new_last_page = ceil($new_total / $per_page);
                    header("Location: manage_rules.php?added=1&page=$new_last_page&per_page=$per_page");
                    exit;
                } else $error = "❌ Failed to add rule.";
                $stmt->close();
            }
        }
    } else $error = "❌ All fields required.";
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM conditional_knowledge WHERE id = $id");
    header("Location: manage_rules.php?deleted=1&page=$current_page&per_page=$per_page");
    exit;
}

if ($search) {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge WHERE condition_if LIKE ? OR consequence_then LIKE ? LIMIT ?, ?");
    $stmt->bind_param("ssii", $like, $like, $offset, $per_page);
} else {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $per_page);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage If-Then Rules</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; vertical-align: top; }
        td:nth-child(2) { width: 50%; }
        td:nth-child(3) { white-space: nowrap; text-align: center; }

        .action-btn {
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 13px;
            display: inline-block;
        }

        .edit-btn { background-color: #007bff; color: white; }
        .edit-btn:hover { background-color: #0056b3; }
        .delete-btn { background-color: #e74c3c; color: white; }
        .delete-btn:hover { background-color: #c0392b; }

        .action-pair {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .search-bar { margin: 15px 0; }
        .search-bar input[type="text"] { padding: 6px; width: 250px; }
        .search-bar button { padding: 6px 12px; font-weight: bold; }
        input[type="text"] { width: 200px; margin-bottom: 10px; }
        button[type="submit"] { margin-top: 5px; }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .pagination select {
            padding: 5px;
            font-size: 14px;
        }

        .pagination-nav {
            text-align: right;
        }

        .pagination-nav a, .pagination-nav span {
            display: inline-block;
            padding: 6px 10px;
            margin: 2px;
            border: 1px solid #4CAF50;
            color: #4CAF50;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination-nav a:hover {
            background-color: #4CAF50;
            color: white;
        }

        .pagination-nav .current {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <h1>Edit Rules</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Manage If-Then Rules</h2>

    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_id) ?>">
        <input type="text" name="condition" placeholder="IF condition..." value="<?= htmlspecialchars($edit_cond) ?>" required>
        <input type="text" name="result" placeholder="THEN result..." value="<?= htmlspecialchars($edit_result) ?>" required>
        <button type="submit" name="add"><?= $edit_id ? 'Update' : 'Add' ?> Rule</button>
    </form>

    <hr style="border-top: 2px solid black; margin: 20px 0;">

    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search IF or THEN..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th style="background-color: #4CAF50; color: white;">If</th>
                <th style="background-color: #4CAF50; color: white;">Then</th>
                <th style="background-color: #4CAF50; color: white;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['condition_if']) ?></td>
                    <td><?= htmlspecialchars($row['consequence_then']) ?></td>
                    <td>
                        <div class="action-pair">
                            <a href="?edit=<?= $row['id'] ?>&page=<?= $current_page ?>&per_page=<?= $per_page ?>" class="action-btn edit-btn">✏️ Edit</a>
                            <a href="?delete=<?= $row['id'] ?>&page=<?= $current_page ?>&per_page=<?= $per_page ?>" onclick="return confirm('Delete this rule?')" class="action-btn delete-btn">❌ Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">❌ No rules found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-container">
        <form method="GET">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            Entries per page:
            <select name="per_page" onchange="this.form.submit()">
                <?php foreach ([10, 20, 50, 100] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $opt == $per_page ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="pagination-nav">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?= $current_page - 1 ?>&per_page=<?= $per_page ?>&search=<?= urlencode($search) ?>">« Prev</a>
            <?php endif; ?>
            <?php
            $range = 2;
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == 1 || $i == $total_pages || abs($i - $current_page) <= $range) {
                    echo $i == $current_page
                        ? "<span class='current'>$i</span>"
                        : "<a href='?page=$i&per_page=$per_page&search=" . urlencode($search) . "'>$i</a>";
                } elseif (abs($i - $current_page) == $range + 1) {
                    echo "<span>...</span>";
                }
            }
            ?>
            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?>&per_page=<?= $per_page ?>&search=<?= urlencode($search) ?>">Next »</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>
</body>
</html>
