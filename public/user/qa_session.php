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
<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Ask a Legal Question</h2>

    <form id="questionForm" method="POST">
        <input type="text" id="legalQuestion" name="question" placeholder="Enter your legal situation..." required>
        <button type="submit">Ask</button>
    </form>

    <div id="predictionResult" style="margin-top: 10px; font-weight: bold;"></div>

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

<script>
document.getElementById("questionForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const question = document.getElementById("legalQuestion").value;
    const resultBox = document.getElementById("predictionResult");

    try {
        const formData = new URLSearchParams();
        formData.append("question", question);

        const response = await fetch("../predict.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString()
        });

        const data = await response.json();
        resultBox.textContent = `Answer: ${data.answer}`;
    } catch (error) {
        resultBox.textContent = "❌ Error: Could not get prediction.";
    }
});
</script>
</body>
</html>
