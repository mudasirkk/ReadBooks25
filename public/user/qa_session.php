<?php
require_once '../includes/auth.php';
require_role('user');
require_once '../includes/db.php';

$answer = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = strtolower(trim($_POST['question']));

    $stmt = $conn->prepare("SELECT condition_text, result_text FROM conditional_knowledge");
    $stmt->execute();
    $result = $stmt->get_result();

    $matched = false;

    while ($row = $result->fetch_assoc()) {
        $if_condition = strtolower($row['condition_text']);

        if (strpos($question, $if_condition) !== false) {
            $answer = strtoupper($row['result_text']);
            $matched = true;
            break;
        }
    }

    if (!$matched) {
        $answer = "MAYBE";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ask a Legal Question</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <h1>Legal QA Assistant</h1>
    </header>

    <div class="container">
        <div class="section">
            <h2 class="section-title">Ask a Question</h2>
            <form method="POST">
                <textarea name="question" placeholder="Describe your legal situation..." rows="4" style="width:100%;" required></textarea><br><br>
                <button type="submit">Submit</button>
            </form>

            <?php if ($answer): ?>
                <div style="margin-top: 20px;">
                    <strong>Answer:</strong> <?php echo $answer; ?>
                </div>
            <?php endif; ?>
        </div>
        <p><a href="../logout.php">Logout</a></p>
    </div>

    <footer>
        <p>© 2025 Read Books Project</p>
    </footer>
</body>
</html>
