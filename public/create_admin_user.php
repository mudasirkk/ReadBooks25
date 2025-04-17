<?php
require_once 'includes/db.php';

$username = 'admin1';
$password = '0000';
$role = 'admin';

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "✅ Admin user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>
