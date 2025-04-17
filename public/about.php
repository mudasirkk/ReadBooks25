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
    <title>About - Web-Based Knowledge Extractor</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>About</h1>
    <hr class="header-line">
</header>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">About Us</h2>
    <p>
        This system is designed to help users extract and reason about legal knowledge from documents or URLs. It supports different user roles such as admins, experts, and general users.
    </p>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>

</body>
</html>
