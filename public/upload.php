<?php
$servername = "cs.newpaltz.edu";
$username = "p_s25_03";  
$password = "43n7xg";  
$dbname = "p_s25_03_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_FILES["uploadedFile"]["error"] == UPLOAD_ERR_OK) {
    $fileName = $_FILES["uploadedFile"]["name"];
    $fileTmpPath = $_FILES["uploadedFile"]["tmp_name"];
    $fileType = $_FILES["uploadedFile"]["type"];
    
    if ($fileType == "text/plain") {
        $extractedText = file_get_contents($fileTmpPath);
    }
    // Extract text from a PDF (Requires `pdftotext` to be installed on your server)
    elseif ($fileType == "application/pdf") {
        $pdfText = shell_exec("pdftotext $fileTmpPath -");
        $extractedText = trim($pdfText);
    } else {
        die("Unsupported file type.");
    }

    if (!empty($extractedText)) {
        
        $stmt = $conn->prepare("INSERT INTO extracted_texts (source_type, source_name, extracted_text) VALUES (?, ?, ?)");
        $sourceType = "FILE";
        $stmt->bind_param("sss", $sourceType, $fileName, $extractedText);
        
        if ($stmt->execute()) {
            echo "Text extracted and saved successfully!";
        } else {
            echo "Error saving text: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "No text found in the file.";
    }
}
$conn->close();
?>
