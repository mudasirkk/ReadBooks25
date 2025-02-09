<?php
$servername = "cs.newpaltz.edu";
$username = "p_s25_03";  
$password = "43n7xg";  
$dbname = "p_s25_03_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['url'])) {
    $url = $_POST['url'];
    
    $html = file_get_contents($url);
    
    $extractedText = strip_tags($html);
    
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
        echo "No text extracted.";
    }
}
$conn->close();
?>
