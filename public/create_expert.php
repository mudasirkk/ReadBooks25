<?php
require_once 'includes/db.php';

$username = 'admin1';
$password = '0000';  
$role = 'admin';

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM p_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "⚠️ Admin user already exists.";
} else {
    $stmt = $conn->prepare("INSERT INTO p_users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "✅ Admin user created successfully!";
    } else {
        echo "❌ Error creating admin: " . $stmt->error;
    }
}
?>
