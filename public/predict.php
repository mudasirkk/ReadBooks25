<?php
header('Content-Type: application/json');
session_start();
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['question'])) {
    $question = escapeshellarg($_POST['question']);
    $command = "python predict.py $question";

    $output = shell_exec($command . " 2>&1");

    file_put_contents("debug_log.txt", "COMMAND: $command\nOUTPUT:\n$output");

    $data = json_decode($output, true);
    if (isset($data["answer"])) {
        $answer = $data["answer"];

        $user_id = $_SESSION['user_id'] ?? null;
        $stmt = $conn->prepare("INSERT INTO qa_history (user_id, question, answer) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $_POST['question'], $answer);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["answer" => $answer]);
    } else {
        echo json_encode(["answer" => "Error", "debug" => $output]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
