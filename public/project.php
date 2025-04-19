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
    <section class="section">
        <h2 class="section-title">What is this Project?</h2>
        <p style="text-align: center; font-size: 16px; line-height: 1.6;">
            This is a web-based tool designed to help users extract legal information from PDFs, text files, and online sources.
            This system is particularly useful for <strong>legal professionals</strong>, <strong>researchers</strong>, and <strong>students</strong>
            who need to analyze property related legal documents efficiently.
        </p>
    </section>

    <hr>

    <section class="section">
        <h2 class="section-title">Features</h2>
        <ul style="font-size: 16px; line-height: 1.8;">
            <li><strong>Upload Legal Documents:</strong> Easily upload PDFs or text files to extract legal content.</li>
            <li><strong>Extract from URLs:</strong> Enter a website URL to extract relevant legal information.</li>
            <li><strong>Role-Based Access:</strong> Admins manage users, experts maintain legal terms and rules, and regular users ask legal questions.</li>
            <li><strong>Legal QA:</strong> Ask legal yes/no questions and receive answers powered by a custom-trained machine learning model.</li>
            <li><strong>History Tracking:</strong> Logged-in users can see a record of their previous legal questions and answers.</li>
        </ul>
    </section>

    <hr>

    <section class="section">
        <h2 class="section-title">Technology Stack</h2>
        <ul style="font-size: 16px; line-height: 1.8;">
            <li>Frontend: HTML, CSS, JavaScript </li>
            <li>Backend: PHP + MySQL</li>
            <li>Legal Intelligence: Python + TensorFlow</li>
            <li>PDF Parsing: Smalot PDF Parser</li>
            <li>Text Processing: NLP with spaCy + custom scripts</li>
        </ul>
    </section>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>

</body>
</html>
