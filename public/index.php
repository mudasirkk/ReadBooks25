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

<!--
    <nav>
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="project.html">Project</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="display_relationships.php">View Relationships</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>
-->
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
            <form id="questionForm">
                <input type="text" id="legalQuestion" placeholder="Enter your legal situation..." required>
                <button type="submit">Ask</button>
            </form>
            <div id="predictionResult" style="margin-top: 10px; font-weight: bold;"></div>
        </div>
<<<<<<< Updated upstream:public/index.php

        <script>
        document.getElementById("questionForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            const question = document.getElementById("legalQuestion").value;
            const resultBox = document.getElementById("predictionResult");

            try {
                const response = await fetch("http://localhost:5000/predict", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ text: question })
                });

                const data = await response.json();
                resultBox.textContent = `Answer: ${data.answer}`;
            } catch (error) {
                resultBox.textContent = "Error connecting to prediction server.";
            }
        });
        </script>
=======
>>>>>>> Stashed changes:public/index.html
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

            const response = await fetch("predict.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
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
