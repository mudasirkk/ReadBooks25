<?php
$host = 'cs.newpaltz.edu';
$db = 'p_s25_03_db';
$user = 'p_s25_03';
$pass = '43n7xg'; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
