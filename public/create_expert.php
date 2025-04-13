<?php
require_once 'includes/db.php';

$username = 'expert1';
$password = 'expert123';
$role = 'expert'; 

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "Expert user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>
