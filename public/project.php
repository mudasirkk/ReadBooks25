<?php
session_start();
require_once 'includes/auth_helpers.php';
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About the Project - Legal Knowledge Extractor</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header>
        <h1>About the Project</h1>
        <hr class="header-line">
    </header>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="section">
        <h2 class="section-title">What is this Project?</h2>
        <p>
            This is a web-based tool designed to help users extract legal information from PDFs, text files, and online sources.
            This system is particularly useful for legal professionals, researchers, and students who need to analyze legal documents efficiently.
        </p>
    </div>

    <br>

    <div class="section">
        <h2 class="section-title">Features</h2>
        <ul>
            <li><strong class="underline">Upload Legal Documents:</strong> Easily upload PDFs or text files to extract legal content.</li>
            <li><strong class="underline">Extract from URLs:</strong> Enter a website URL to extract relevant legal information.</li>
        </ul>
    </div>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>

</body>
</html>
