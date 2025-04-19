
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM p_users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $hashed, $role);
                $stmt->fetch();
                if (password_verify($password, $hashed)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        } else {
            $error = "Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Login</h1>
    <hr class="header-line">
</header>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Login</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" id="password" placeholder="Password" required><br>
        <label><input type="checkbox" onclick="togglePassword()"> Show Password</label><br>
        <button type="submit">Login</button>
    </form>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>

<script>
function togglePassword() {
    var pw = document.getElementById("password");
    pw.type = pw.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
