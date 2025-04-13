<?php
require_once '../includes/auth.php';
require_role('expert');
require_once '../includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $term = trim($_POST['term']);
    $definition = trim($_POST['definition']);

    if ($term && $definition) {
        $stmt = $conn->prepare("INSERT INTO terminology (term, definition) VALUES (?, ?)");
        $stmt->bind_param("ss", $term, $definition);
        $stmt->execute() ? $success = "Term added!" : $error = "Failed to add term.";
    } else {
        $error = "All fields are required.";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM terminology WHERE id = $id");
    $success = "Term deleted.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Terms</title>
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
    <h2>Manage Terms</h2>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="term" placeholder="Term" required>
        <input type="text" name="definition" placeholder="Definition" required>
        <button type="submit" name="add">Add Term</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Term</th>
                <th>Definition</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $result = $conn->query("SELECT id, term, definition FROM terminology");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['term']}</td>
                    <td>{$row['definition']}</td>
                    <td>
                        <a href='?delete={$row['id']}' onclick='return confirm(\"Delete this term?\")'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
