<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM p_users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: manage_users.php");
exit;
