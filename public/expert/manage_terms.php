<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
require_once '../includes/db.php';

$success = '';
$error = '';
$edit_id = '';
$edit_term = '';
$edit_desc = '';
$search = $_GET['search'] ?? '';
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
$offset = ($current_page - 1) * $per_page;

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT term, description FROM terminology WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_term, $edit_desc);
    $stmt->fetch();
    $stmt->close();
}

$count_stmt = $search
    ? $conn->prepare("SELECT COUNT(*) FROM terminology WHERE term LIKE ? OR description LIKE ?")
    : $conn->prepare("SELECT COUNT(*) FROM terminology");

if ($search) {
    $like = "%$search%";
    $count_stmt->bind_param("ss", $like, $like);
}
$count_stmt->execute();
$count_stmt->bind_result($total_rows);
$count_stmt->fetch();
$count_stmt->close();

$total_pages = ceil($total_rows / $per_page);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term = trim($_POST['term']);
    $description = trim($_POST['description']);
    $term_id = $_POST['id'] ?? null;

    if ($term && $description) {
        if (!$term_id) {
            $check = $conn->prepare("SELECT id FROM terminology WHERE term = ?");
            $check->bind_param("s", $term);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $error = "❌ Term already exists.";
            }
            $check->close();
        }

        if (!$error) {
            if ($term_id) {
                $stmt = $conn->prepare("UPDATE terminology SET term=?, description=? WHERE id=?");
                $stmt->bind_param("ssi", $term, $description, $term_id);
                if ($stmt->execute()) {
                    header("Location: manage_terms.php?updated=1&page=$current_page&per_page=$per_page");
                    exit;
                } else {
                    $error = "❌ Update failed.";
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO terminology (term, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $term, $description);
                if ($stmt->execute()) {
                    // Recount to get last page
                    $count = $conn->query("SELECT COUNT(*) as count FROM terminology")->fetch_assoc()['count'];
                    $last_page = ceil($count / $per_page);
                    header("Location: manage_terms.php?added=1&page=$last_page&per_page=$per_page");
                    exit;
                } else {
                    $error = "❌ Add failed.";
                }
                $stmt->close();
            }
        }
    } else {
        $error = "❌ All fields are required.";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM terminology WHERE id = $id");
    header("Location: manage_terms.php?deleted=1&page=$current_page&per_page=$per_page");
    exit;
}

if ($search) {
    $stmt = $conn->prepare("SELECT id, term, description FROM terminology WHERE term LIKE ? OR description LIKE ? LIMIT ?, ?");
    $stmt->bind_param("ssii", $like, $like, $offset, $per_page);
} else {
    $stmt = $conn->prepare("SELECT id, term, description FROM terminology LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $per_page);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Terms</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            vertical-align: top;
            text-align: left;
        }
        td:nth-child(2) {
            width: 65%;
        }
        td:nth-child(3) {
            white-space: nowrap;
            text-align: center;
        }
        .action-btn {
            padding: 5px 10px;
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
            justify-content: center;
            gap: 8px;
        }
        .search-bar {
            margin: 15px 0;
        }
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .pagination {
            display: flex;
            gap: 5px;
        }
        .pagination a, .pagination span {
            padding: 6px 10px;
            border: 1px solid #4CAF50;
            color: #4CAF50;
            text-decoration: none;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
        }
        .pagination .current {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <h1>Edit Terms</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Manage Terms</h2>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_id) ?>">
        <input type="text" name="term" placeholder="Term" value="<?= htmlspecialchars($edit_term) ?>" required>
        <input type="text" name="description" placeholder="Description" value="<?= htmlspecialchars($edit_desc) ?>" required>
        <button type="submit" name="add"><?= $edit_id ? 'Update' : 'Add' ?> Term</button>
    </form>

    <hr style="border-top: 2px solid black; margin-top: 20px; margin-bottom: 20px;">

    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search term or description..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th style="background-color: #4CAF50; color: white;">Term</th>
                <th style="background-color: #4CAF50; color: white;">Description</th>
                <th style="background-color: #4CAF50; color: white;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . htmlspecialchars($row['term']) . "</td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td>
                        <div class='action-pair'>
                            <a href='?edit={$row['id']}&page=$current_page&per_page=$per_page' class='action-btn edit-btn'>✏️ Edit</a>
                            <a href='?delete={$row['id']}&page=$current_page&per_page=$per_page' onclick='return confirm(\"Delete this term?\")' class='action-btn delete-btn'>❌ Delete</a>
                        </div>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>❌ No terms found.</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <form method="GET" style="margin: 0;">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <label>Entries per page:
                <select name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 20, 50, 100] as $option): ?>
                        <option value="<?= $option ?>" <?= $per_page == $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <div class="pagination">
            <?php
            if ($current_page > 1) {
                echo "<a href='?page=" . ($current_page - 1) . "&per_page=$per_page&search=$search'>« Prev</a>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo "<span class='current'>$i</span>";
                } else {
                    echo "<a href='?page=$i&per_page=$per_page&search=$search'>$i</a>";
                }
            }

            if ($current_page < $total_pages) {
                echo "<a href='?page=" . ($current_page + 1) . "&per_page=$per_page&search=$search'>Next »</a>";
            }
            ?>
        </div>
    </div>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>
</body>
</html>
