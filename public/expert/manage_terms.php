<?php
require_once '../includes/auth_helpers.php';
require_any_role('expert', 'admin');
require_once '../includes/db.php';

$success = '';
$error = '';
$edit_id = '';
$edit_term = '';
$edit_desc = '';

if (isset($_GET['updated'])) {
    $success = "✅ Term updated.";
}

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
                    header("Location: manage_terms.php?updated=1");
                    exit;
                } else {
                    $error = "❌ Update failed.";
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO terminology (term, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $term, $description);
                if ($stmt->execute()) {
                    $success = "✅ Term added!";
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

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT term, description FROM terminology WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_term, $edit_desc);
    $stmt->fetch();
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM terminology WHERE id = $id");
    $success = "✅ Term deleted.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Terms</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container">
    <h2 class="section-title">Manage Terms</h2>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_id) ?>">
        <input type="text" name="term" placeholder="Term" value="<?= htmlspecialchars($edit_term) ?>" required>
        <input type="text" name="description" placeholder="Description" value="<?= htmlspecialchars($edit_desc) ?>" required>
        <button type="submit" name="add"> <?= $edit_id ? 'Update' : 'Add' ?> Term</button>
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
    $result = $conn->query("SELECT id, term, description FROM terminology");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['term']) . "</td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td>
                        ✏️ <a href='?edit={$row['id']}' style='color:#007bff;'>Edit</a> |
                        ❌ <a href='?delete={$row['id']}' onclick='return confirm(\"Delete this term?\")' style='color:#dc3545;'>Delete</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>❌ Failed to fetch terms: " . $conn->error . "</td></tr>";
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
