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
    <style>
        .rules-box {
            background-color: #f4f4f4;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .rules-box ul {
            margin: 0;
            padding-left: 20px;
        }
        #loading-bar-container {
            display: none;
            width: 100%;
            background-color: #ddd;
            margin-top: 15px;
            border-radius: 5px;
        }
        #loading-bar {
            width: 0%;
            height: 10px;
            background-color: #007bff;
            border-radius: 5px;
        }
    </style>
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
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" name="uploadedFile" required>
            <button type="submit">Upload</button>
        </form>
        <div id="uploadResponse" style="margin-top: 10px; font-weight: bold;"></div>
    </div>

    <hr>

    <div class="section">
        <h2>Extract Legal Text from URL</h2>
        <form id="urlForm">
            <input type="text" name="url" placeholder="Enter URL" required>
            <button type="submit">Extract</button>
        </form>
        <div id="urlResponse" style="margin-top: 10px; font-weight: bold;"></div>
    </div>

    <hr>

    <div class="section">
        <h2>Ask a Legal Question</h2>
        <form method="POST" onsubmit="showLoadingBar()">
            <input type="text" name="question" placeholder="Enter your legal situation..." required>
            <button type="submit">Ask</button>
        </form>

        <!-- Loading bar -->
        <div id="loading-bar-container">
            <div id="loading-bar"></div>
        </div>

        <div style="margin-top: 15px;">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['question'])) {
                $raw_question = $_POST['question'];
                $escaped_question = escapeshellarg($raw_question);
                $command = "/home/khalidt2/venvs/legalqa/bin/python predict.py $escaped_question";
                $output = shell_exec($command . " 2>&1");

                preg_match('/\{.*\}/s', $output, $matches);
                $json = $matches[0] ?? null;
                $data = json_decode($json, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($data["answer"])) {
                    $answer = htmlspecialchars($data["answer"]);
                    $confidence = htmlspecialchars($data["confidence"]);
                    $rule = isset($data["rule"]) ? htmlspecialchars($data["rule"]) : '';

                    echo "<script>document.getElementById('loading-bar-container').style.display = 'none';</script>";
                    echo "<p><strong>Question:</strong> " . htmlspecialchars($raw_question) . "</p>";
                    echo "<p><strong>Answer:</strong> $answer</p>";
                    echo "<p><strong>Confidence:</strong> $confidence%</p>";
                    echo "<p><strong>Matched Rule:</strong> $rule</p>";

                    if (!empty($data["rules"]) && is_array($data["rules"])) {
                        echo "<div class='rules-box'><strong>Legal Insight:</strong><ul>";
                        foreach ($data["rules"] as $r) {
                            echo "<li>" . htmlspecialchars($r) . "</li>";
                        }
                        echo "</ul></div>";
                    }

                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        $stmt = $conn->prepare("INSERT INTO qa_history (user_id, question, answer) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $user_id, $raw_question, $answer);
                        $stmt->execute();
                        $stmt->close();
                    }
                } else {
                    echo "<p style='color:red;'>❌ Error: Could not get prediction.</p>";
                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                }
            }
            ?>
        </div>
    </div>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>

<!-- AJAX and Loading Bar Script -->
<script>
document.getElementById("uploadForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const responseBox = document.getElementById("uploadResponse");

    try {
        const response = await fetch("upload.php", {
            method: "POST",
            body: formData
        });
        const text = await response.text();
        responseBox.textContent = text;
        responseBox.style.color = text.includes("✅") ? "green" : "red";
    } catch (error) {
        responseBox.textContent = "❌ Failed to upload file.";
        responseBox.style.color = "red";
    }
});

document.getElementById("urlForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const formData = new URLSearchParams(new FormData(this));
    const responseBox = document.getElementById("urlResponse");

    try {
        const response = await fetch("extract-url.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData
        });
        const text = await response.text();
        responseBox.textContent = text;
        responseBox.style.color = text.includes("✅") ? "green" : "red";
    } catch (error) {
        responseBox.textContent = "❌ Failed to extract from URL.";
        responseBox.style.color = "red";
    }
});

function showLoadingBar() {
    const container = document.getElementById("loading-bar-container");
    const bar = document.getElementById("loading-bar");

    container.style.display = "block";
    bar.style.width = "0%";
    let width = 0;

    const interval = setInterval(() => {
        if (width >= 90) return;
        width += 1;
        bar.style.width = width + "%";
    }, 15);

    window.addEventListener("load", () => {
        clearInterval(interval);
        bar.style.width = "100%";
        setTimeout(() => {
            container.style.display = "none";
            bar.style.width = "0%";
        }, 400);
    });
}
</script>

</body>
</html>
