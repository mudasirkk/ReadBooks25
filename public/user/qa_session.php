<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/config.php';
require_once '../includes/auth_helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Legal QA Session</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<header>
    <h1>Q&A Session</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Ask a Legal Question</h2>

    <form method="POST">
        <input type="text" name="question" placeholder="Enter your legal situation..." required>
        <button type="submit">Ask</button>
    </form>

    <div style="margin-top: 15px;">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['question'])) {
            $raw_question = $_POST['question'];
            $escaped_question = escapeshellarg($raw_question);
            $command = "/home/khalidt2/python39/bin/python3.9 predict.py $escaped_question";
            $output = shell_exec($command . " 2>&1");

            $data = json_decode($output, true);
            if (isset($data["answer"])) {
                $answer = htmlspecialchars($data["answer"]);
                echo "<p><strong>Answer:</strong> $answer</p>";

                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("INSERT INTO qa_history (user_id, question, answer) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $user_id, $raw_question, $answer);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                echo "<p style='color:red;'>❌ Error: Could not get prediction.</p>";
                echo "<pre>" . htmlspecialchars($output) . "</pre>"; // for debugging
            }
        }
        ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <h3 class="section-title">Your Question History</h3>
        <table>
            <thead>
                <tr>
                    <th style="background-color: #4CAF50; color: white;">Question</th>
                    <th style="background-color: #4CAF50; color: white;">Answer</th>
                    <th style="background-color: #4CAF50; color: white;">Time</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $uid = $_SESSION['user_id'];
            $result = $conn->query("SELECT question, answer, created_at FROM qa_history WHERE user_id = $uid ORDER BY created_at DESC");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['question']) . "</td>
                        <td>" . htmlspecialchars($row['answer']) . "</td>
                        <td>" . htmlspecialchars($row['created_at']) . "</td>
                      </tr>";
            }
            ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>
</body>
</html>
