<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['question'])) {
    $question = escapeshellarg($_POST['question']);
    $command = "python predict.py $question";

    $output = shell_exec($command . " 2>&1");

    file_put_contents("debug_log.txt", "COMMAND: $command\nOUTPUT:\n$output");

    $data = json_decode($output, true);
    if (isset($data["answer"])) {
        echo json_encode(["answer" => $data["answer"]]);
    } else {
        echo json_encode(["answer" => "Error", "debug" => $output]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
