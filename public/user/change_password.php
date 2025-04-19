
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_helpers.php';

if (!is_logged_in() || is_admin()) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $new_password)) {
        $error = "Password does not meet the security requirements.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE p_users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        if ($stmt->execute()) {
            $success = "Password successfully updated.";
        } else {
            $error = "Failed to update password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<header>
    <h1>Change Password</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Change Password</h2>

    <?php if ($success): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="new_password" id="new_password" placeholder="New Password" required><br>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required><br>
        <label><input type="checkbox" onclick="togglePassword()"> Show Password</label>

        <div class="guidelines" style="text-align: left; margin-top: 10px;">
            <p><strong>Password must include:</strong></p>
            <ul>
                <li id="length" style="color:red;">At least 8 characters</li>
                <li id="uppercase" style="color:red;">At least 1 uppercase letter</li>
                <li id="lowercase" style="color:red;">At least 1 lowercase letter</li>
                <li id="number" style="color:red;">At least 1 number</li>
                <li id="special" style="color:red;">At least 1 special character</li>
            </ul>
        </div>
        <button type="submit">Update Password</button>
    </form>
</div>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>

<script>
function togglePassword() {
    var p1 = document.getElementById("new_password");
    var p2 = document.getElementById("confirm_password");
    p1.type = p1.type === "password" ? "text" : "password";
    p2.type = p2.type === "password" ? "text" : "password";
}

document.getElementById("new_password").addEventListener("input", function () {
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
