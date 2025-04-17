<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
require_once '../includes/db.php';

$success = '';
$error = '';
$edit_id = '';
$edit_cond = '';
$edit_result = '';

if (isset($_GET['updated'])) {
    $success = "✅ Rule updated.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $condition = trim($_POST['condition']);
    $result = trim($_POST['result']);
    $rule_id = $_POST['id'] ?? null;

    if ($condition && $result) {
        if (!$rule_id) {
            $check = $conn->prepare("SELECT id FROM conditional_knowledge WHERE condition_if = ? AND consequence_then = ?");
            $check->bind_param("ss", $condition, $result);
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
                $stmt->bind_param("ssi", $condition, $result, $rule_id);
                if ($stmt->execute()) {
                    header("Location: manage_rules.php?updated=1");
                    exit;
                } else {
                    $error = "❌ Update failed.";
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO conditional_knowledge (condition_if, consequence_then) VALUES (?, ?)");
                $stmt->bind_param("ss", $condition, $result);
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
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container">
    <h2 class="section-title">Manage If-Then Rules</h2>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_id) ?>">
        <input type="text" name="condition" placeholder="IF condition..." value="<?= htmlspecialchars($edit_cond) ?>" required>
        <input type="text" name="result" placeholder="THEN result..." value="<?= htmlspecialchars($edit_result) ?>" required>
        <button type="submit" name="add"> <?= $edit_id ? 'Update' : 'Add' ?> Rule</button>
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
        $result = $conn->query("SELECT id, condition_if, consequence_then FROM conditional_knowledge");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . htmlspecialchars($row['condition_if']) . "</td>
                    <td>" . htmlspecialchars($row['consequence_then']) . "</td>
                    <td>
                       ✏️ <a href='?edit={$row['id']}'>Edit</a> |
                       ❌ <a href='?delete={$row['id']}' onclick='return confirm(\"Delete this rule?\")'>Delete</a>
                    </td>
                  </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>❌ Failed to fetch rules: " . $conn->error . "</td></tr>";
        }
        ?>
        </tbody>
    </table>

</div>
<footer>
    <p>© 2025 Read Books Project</p>
</footer>
</body>
</html>
