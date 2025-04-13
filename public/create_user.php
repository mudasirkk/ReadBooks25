<?php
require_once 'includes/db.php';

$username = 'user1';
$password = 'user123';
$role = 'user'; 

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "Regular user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>
