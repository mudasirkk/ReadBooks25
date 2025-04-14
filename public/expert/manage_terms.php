<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
require_once '../includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term = trim($_POST['term']);
    $definition = trim($_POST['definition']);
    $term_id = $_POST['id'] ?? null;

    if ($term && $definition) {
        if (!$term_id) {
            $check = $conn->prepare("SELECT id FROM terminology WHERE term = ?");
            $check->bind_param("s", $term);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $error = "❌ Term already exists.";
            }
        }

        if (!$error) {
            if ($term_id) {
                $stmt = $conn->prepare("UPDATE terminology SET term=?, definition=? WHERE id=?");
                $stmt->bind_param("ssi", $term, $definition, $term_id);
                $stmt->execute() ? $success = "✅ Term updated." : $error = "❌ Update failed.";
            } else {
                $stmt = $conn->prepare("INSERT INTO terminology (term, definition) VALUES (?, ?)");
                $stmt->bind_param("ss", $term, $definition);
                $stmt->execute() ? $success = "✅ Term added!" : $error = "❌ Add failed.";
            }
        }
    } else {
        $error = "❌ All fields are required.";
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
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
    <h2>Manage Terms</h2>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <?php
    $edit_id = $_GET['edit'] ?? '';
    $edit_term = '';
    $edit_def = '';

    if ($edit_id) {
        $stmt = $conn->prepare("SELECT term, definition FROM terminology WHERE id=?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $stmt->bind_result($edit_term, $edit_def);
        $stmt->fetch();
    }
    ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_id) ?>">
        <input type="text" name="term" placeholder="Term" value="<?= htmlspecialchars($edit_term) ?>" required>
        <input type="text" name="definition" placeholder="Definition" value="<?= htmlspecialchars($edit_def) ?>" required>
        <button type="submit" name="add"><?= $edit_id ? 'Update' : 'Add' ?> Term</button>
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
                        <a href='?edit={$row['id']}'>Edit</a> |
                        <a href='?delete={$row['id']}' onclick='return confirm(\"Delete this term?\")'>Delete</a>
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