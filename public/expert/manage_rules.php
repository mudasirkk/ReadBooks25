<?php
require_once '../includes/auth.php';
require_role('expert');
require_once '../includes/db.php';

$success = '';
$error = '';

// Add rule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $condition = trim($_POST['condition']);
    $result = trim($_POST['result']);

    if ($condition && $result) {
        $stmt = $conn->prepare("INSERT INTO conditional_knowledge (condition_text, result_text) VALUES (?, ?)");
        $stmt->bind_param("ss", $condition, $result);
        $stmt->execute() ? $success = "Rule added!" : $error = "Failed to add rule.";
    } else {
        $error = "All fields required.";
    }
}

// Delete rule
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM conditional_knowledge WHERE id = $id");
    $success = "Rule deleted.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Rules</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #eee; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Manage If-Then Rules</h2>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="condition" placeholder="IF condition..." required>
        <input type="text" name="result" placeholder="THEN result..." required>
        <button type="submit" name="add">Add Rule</button>
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
                        <a href='?delete={$row['id']}' onclick='return confirm(\"Delete this rule?\")'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
