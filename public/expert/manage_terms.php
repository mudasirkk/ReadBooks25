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

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT term, description FROM terminology WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_term, $edit_desc);
    $stmt->fetch();
    $stmt->close();
}

if (isset($_GET['updated'])) {
    $success = "✅ Term updated.";
} elseif (isset($_GET['added'])) {
    $success = "✅ Term added!";
} elseif (isset($_GET['deleted'])) {
    $success = "✅ Term deleted.";
}

if ($search) {
    $stmt = $conn->prepare("SELECT id, term, description FROM terminology WHERE term LIKE ? OR description LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT id, term, description FROM terminology");
}
$stmt->execute();
$result = $stmt->get_result();

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
                    $success = "✅ Term updated.";
                    $edit_id = '';
                    $edit_term = '';
                    $edit_desc = '';
                    if ($search) {
                        $stmt = $conn->prepare("SELECT id, term, description FROM terminology WHERE term LIKE ? OR description LIKE ?");
                        $like = "%$search%";
                        $stmt->bind_param("ss", $like, $like);
                    } else {
                        $stmt = $conn->prepare("SELECT id, term, description FROM terminology");
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $error = "❌ Update failed.";
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO terminology (term, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $term, $description);
                if ($stmt->execute()) {
                    header("Location: manage_terms.php?added=1");
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
    header("Location: manage_terms.php?deleted=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Terms</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        table {
            width: 100%;
            table-layout: fixed;
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

        .icon {
            margin-right: 5px;
        }

        .action-pair {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .action-separator {
            color: #333;
            font-weight: bold;
        }

        input[type="text"] {
            width: 200px;
            margin-bottom: 10px;
        }

        .search-bar {
            margin: 20px 0;
        }

        .search-bar input[type="text"] {
            width: 250px;
            padding: 6px;
        }

        .search-bar button {
            padding: 6px 12px;
            font-weight: bold;
        }

        button[type="submit"] {
            margin-top: 5px;
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
    <colgroup>
        <col style="width: 20%;">
        <col style="width: 58%;">
        <col style="width: 22%;">
    </colgroup>
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
                                <a href='?edit={$row['id']}' class='action-btn edit-btn'>
                                    <span class='icon'>✏️</span> Edit
                                </a>
                                <span class='action-separator'>|</span>
                                <a href='?delete={$row['id']}' class='action-btn delete-btn' onclick='return confirm(\"Delete this term?\")'>
                                    <span class='icon'>❌</span> Delete
                                </a>
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
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>
</body>
</html>
