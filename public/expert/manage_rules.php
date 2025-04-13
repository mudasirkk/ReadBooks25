<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
require_once '../includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $condition = htmlspecialchars(trim($_POST['condition']), ENT_QUOTES, 'UTF-8');
    $result_text = htmlspecialchars(trim($_POST['result']), ENT_QUOTES, 'UTF-8');
    $rule_id = isset($_POST['id']) ? intval($_POST['id']) : null;

    if ($condition && $result_text) {
        if (!$rule_id) {
            $check = $conn->prepare("SELECT id FROM conditional_knowledge WHERE condition_text = ? AND result_text = ?");
            $check->bind_param("ss", $condition, $result_text);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $error = "❌ This rule already exists.";
            }
        }

        if (!$error) {
            if ($rule_id) {
                $stmt = $conn->prepare("UPDATE conditional_knowledge SET condition_text=?, result_text=? WHERE id=?");
                $stmt->bind_param("ssi", $condition, $result_text, $rule_id);
                $stmt->execute() ? $success = "✅ Rule updated." : $error = "❌ Update failed.";
            } else {
                $stmt = $conn->prepare("INSERT INTO conditional_knowledge (condition_text, result_text) VALUES (?, ?)");
                $stmt->bind_param("ss", $condition, $result_text);
                $stmt->execute() ? $success = "✅ Rule added!" : $error = "❌ Failed to add rule.";
            }
        }
    } else {
        $error = "❌ All fields required.";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM conditional_knowledge WHERE id = $id");
    $success = "Rule deleted.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage If-Then Rules</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
    <h2>Manage If-Then Rules</h2>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <?php
    $edit_id = $_GET['edit'] ?? '';
    $edit_cond = '';
    $edit_result = '';

    if ($edit_id) {
        $stmt = $conn->prepare("SELECT condition_text, result_text FROM conditional_knowledge WHERE id=?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $stmt->bind_result($edit_cond, $edit_result);
        $stmt->fetch();
    }
    ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_id) ?>">
        <input type="text" name="condition" placeholder="IF condition..." value="<?= htmlspecialchars($edit_cond) ?>" required>
        <input type="text" name="result" placeholder="THEN result..." value="<?= htmlspecialchars($edit_result) ?>" required>
        <button type="submit" name="add"><?= $edit_id ? 'Update' : 'Add' ?> Rule</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>IF</th>
                <th>THEN</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT id, condition_text, result_text FROM conditional_knowledge");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['condition_text']}</td>
                    <td>{$row['result_text']}</td>
                    <td>
                        <a href='?edit={$row['id']}'>Edit</a> |
                        <a href='?delete={$row['id']}' onclick='return confirm(\"Delete this rule?\")'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>