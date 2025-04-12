<?php
$servername = "cs.newpaltz.edu";
$username = "p_s25_03";
$password = "43n7xg";
$dbname = "p_s25_03_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Relationships</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .sentence {
            font-style: italic;
            color: #333;
        }
    </style>
</head>
<body>
    <h2>Extracted Relationships</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Sentence</th>
            <th>Subject</th>
            <th>Object</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td class="sentence"><?= htmlspecialchars($row['relationship']) ?></td>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['object']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php $conn->close(); ?>
