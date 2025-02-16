<?php
$servername = "cs.newpaltz.edu";
$username = "p_s25_03";  
$password = "43n7xg";  
$dbname = "p_s25_03_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function cleanExtractedText($text) {
    $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
    $text = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $text);
    $text = preg_replace('/<!--.*?-->/s', '', $text);
    $text = preg_replace('/<[^>]+>/', '', $text);
    $text = preg_replace('/https?:\/\/\S+|www\.\S+/', '', $text);
    $text = preg_replace('/\S+@\S+\.\S+/', '', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim($text);
}

if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $html = @file_get_contents($url);

    if ($html === FALSE) {
        die("Failed to fetch URL content.");
    }

    $extractedText = cleanExtractedText($html);

    if (!empty($extractedText)) {
        $stmt = $conn->prepare("INSERT INTO extracted_texts (source_type, source_name, extracted_text) VALUES (?, ?, ?)");
        $sourceType = "URL";
        $stmt->bind_param("sss", $sourceType, $url, $extractedText);

        if ($stmt->execute()) {
            echo "Text extracted from URL and saved successfully!";
        } else {
            echo "Error saving text: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "No meaningful text found on the URL.";
    }
}

$conn->close();
?>
