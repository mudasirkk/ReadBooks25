
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
        $errors[] = "Password does not meet the security requirements.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, 'user')");
        if ($stmt) {
            $stmt->bind_param("ss", $username, $hashed_password);
            if ($stmt->execute()) {
                $success = "✅ Registered successfully. You can now <a href='login.php'>log in</a>.";
            } else {
                $errors[] = "❌ Registration failed. Username may already exist.";
            }
            $stmt->close();
        } else {
            $errors[] = "❌ Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Register</h1>
    <hr class="header-line">
</header>

<?php include 'includes/navbar.php'; ?>
<div class="container">
    <h2 class="section-title">Register</h2>
    <?php foreach ($errors as $error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" id="password" placeholder="New Password" required><br>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required><br>
        <label><input type="checkbox" onclick="togglePassword()"> Show Password</label>

        <div class="guidelines" style="text-align:left; margin-top:10px;">
            <p><strong>Password must include:</strong></p>
            <ul>
                <li id="length" style="color:red;">At least 8 characters</li>
                <li id="uppercase" style="color:red;">At least 1 uppercase letter</li>
                <li id="lowercase" style="color:red;">At least 1 lowercase letter</li>
                <li id="number" style="color:red;">At least 1 number</li>
                <li id="special" style="color:red;">At least 1 special character</li>
            </ul>
        </div>
        <button type="submit">Register</button>
    </form>
</div>

<footer><p>© 2025 Read Books Project</p></footer>

<script>
function togglePassword() {
    var p1 = document.getElementById("password");
    var p2 = document.getElementById("confirm_password");
    p1.type = p1.type === "password" ? "text" : "password";
    p2.type = p2.type === "password" ? "text" : "password";
}

document.getElementById("password").addEventListener("input", function () {
    const pw = this.value;
    document.getElementById("length").style.color = pw.length >= 8 ? "green" : "red";
    document.getElementById("uppercase").style.color = /[A-Z]/.test(pw) ? "green" : "red";
    document.getElementById("lowercase").style.color = /[a-z]/.test(pw) ? "green" : "red";
    document.getElementById("number").style.color = /[0-9]/.test(pw) ? "green" : "red";
    document.getElementById("special").style.color = /[^A-Za-z0-9]/.test(pw) ? "green" : "red";
});
</script>
</body>
</html>
