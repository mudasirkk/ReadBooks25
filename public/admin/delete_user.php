<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;
$id = intval($id);

$check = $conn->prepare("SELECT username FROM p_users WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$check->bind_result($username);
$check->fetch();
$check->close();

if ($username === 'admin1') {
    echo "<h3>❌ You cannot delete the protected admin account.</h3>";
    exit;
}

$stmt = $conn->prepare("DELETE FROM p_users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: manage_users.php");
exit;
