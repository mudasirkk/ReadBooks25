<?php
session_start();
require_once 'includes/auth_helpers.php';
require_once 'includes/config.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web-Based Knowledge Extractor</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Web-Based Knowledge Extractor</h1>
    <hr class="header-line">
</header>

<?php include 'includes/navbar.php'; ?>

<div class="container">

    <div class="section">
        <h2>Upload a PDF or Text File</h2>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="uploadedFile" required>
            <button type="submit">Upload</button>
        </form>
    </div>

    <hr>

    <div class="section">
        <h2>Extract Legal Text from URL</h2>
        <form action="extract-url.php" method="POST">
            <input type="text" name="url" placeholder="Enter URL" required>
            <button type="submit">Extract</button>
        </form>
    </div>

    <hr>

    <div class="section">
        <h2>Ask a Legal Question</h2>
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
    </div>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>
</body>
</html>
