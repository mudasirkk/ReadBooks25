<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';
require_once 'includes/config.php';
?>
<?php
$sql = "SELECT 
            r.id, 
            c1.name AS subject, 
            r.relationship, 
            c2.name AS object
        FROM relationships r
        JOIN concepts c1 ON r.token1 = c1.c_id
        JOIN concepts c2 ON r.token2 = c2.c_id
        ORDER BY r.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Extracted Relationships</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Relationships Table</h1>
    <hr class="header-line">
</header>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Extracted Relationships</h2>

    <table>
        <thead>
            <tr>
                <th style="background-color: #4CAF50; color: white;">ID</th>
                <th style="background-color: #4CAF50; color: white;">Sentence</th>
                <th style="background-color: #4CAF50; color: white;">Subject</th>
                <th style="background-color: #4CAF50; color: white;">Object</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td style="font-style: italic;"><?= htmlspecialchars($row['relationship']) ?></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td><?= htmlspecialchars($row['object']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>
</body>
</html>

<?php $conn->close(); ?>
