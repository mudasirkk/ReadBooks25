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

if (isset($_GET['updated'])) {
    $success = "✅ Rule updated.";
}

if ($search) {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge WHERE condition_if LIKE ? OR consequence_then LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge");
}
$stmt->execute();
$result = $stmt->get_result();

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
            if ($check->num_rows > 0) {
                $error = "❌ This rule already exists.";
            }
            $check->close();
        }

        if (!$error) {
            if ($rule_id) {
                $stmt = $conn->prepare("UPDATE conditional_knowledge SET condition_if=?, consequence_then=? WHERE id=?");
                $stmt->bind_param("ssi", $condition, $result_text, $rule_id);
                if ($stmt->execute()) {
                    header("Location: manage_rules.php?updated=1");
                    exit;
                } else {
                    $error = "❌ Update failed.";
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO conditional_knowledge (condition_if, consequence_then) VALUES (?, ?)");
                $stmt->bind_param("ss", $condition, $result_text);
                if ($stmt->execute()) {
                    $success = "✅ Rule added!";
                } else {
                    $error = "❌ Failed to add rule.";
                }
                $stmt->close();
            }
        }
    } else {
        $error = "❌ All fields required.";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM conditional_knowledge WHERE id = $id");
    $success = "✅ Rule deleted.";
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage If-Then Rules</title>
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
            width: 50%;
        }

        td:nth-child(3) {
            white-space: nowrap;
            text-align: center;
        }

        .action-btn {
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 13px;
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

        .action-separator {
            font-weight: bold;
            color: #333;
        }

        .search-bar {
            margin: 15px 0;
        }

        .search-bar input[type="text"] {
            padding: 6px;
            width: 250px;
        }

        .search-bar button {
            padding: 6px 12px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 200px;
            margin-bottom: 10px;
        }

        button[type="submit"] {
            margin-top: 5px;
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
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . htmlspecialchars($row['condition_if']) . "</td>
                    <td>" . htmlspecialchars($row['consequence_then']) . "</td>
                    <td>
                        <div class='action-pair'>
                            <a href='?edit={$row['id']}' class='action-btn edit-btn'>✏️ Edit</a>
                            <span class='action-separator'>|</span>
                            <a href='?delete={$row['id']}' onclick='return confirm(\"Delete this rule?\")' class='action-btn delete-btn'>❌ Delete</a>
                        </div>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>❌ No rules found.</td></tr>";
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
