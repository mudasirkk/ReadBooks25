<?php
header('Content-Type: application/json');
session_start();
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['question'])) {
    $question = $_POST['question'];
    $payload = json_encode(["text" => $question]);

    $ch = curl_init("http://localhost:5000/predict");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data["answer"])) {
        $answer = $data["answer"];
        $user_id = $_SESSION['user_id'] ?? null;

        $stmt = $conn->prepare("INSERT INTO qa_history (user_id, question, answer) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $question, $answer);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["answer" => $answer]);
    } else {
        echo json_encode(["answer" => "Error", "debug" => $response]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
