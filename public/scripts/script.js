// Function to extract text from PDF file
async function extractTextFromPDF(pdfFile) {
    const reader = new FileReader();

    reader.onload = async function () {
        const typedarray = new Uint8Array(this.result);

        // Load PDF using pdf.js
        const pdf = await pdfjsLib.getDocument(typedarray).promise;
        let extractedText = "";

        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const textContent = await page.getTextContent();
            const textItems = textContent.items.map((item) => item.str);
            extractedText += textItems.join(" ") + "\n";
        }

        displayExtractedText(extractedText);
        sendToServer("PDF", pdfFile.name, extractedText);
    };

    reader.readAsArrayBuffer(pdfFile);
}

// Function to extract text from .txt file
function extractTextFromTXT(txtFile) {
    const reader = new FileReader();

    reader.onload = function () {
        const extractedText = reader.result;
        displayExtractedText(extractedText);
        sendToServer("Text File", txtFile.name, extractedText);
    };

    reader.readAsText(txtFile);
}

// Function to extract text from a URL
async function extractTextFromURL() {
    const url = document.getElementById("urlInput").value;

    if (!url) {
        alert("Please enter a URL");
        return;
    }

    try {
        const response = await fetch("/extract-url", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url }),
        });

        const data = await response.json();
        if (data.error) {
            alert("Error: " + data.error);
        } else {
            displayExtractedText(data.text);
            sendToServer("URL", url, data.text);
        }
    } catch (error) {
        console.error("Error fetching URL:", error);
        alert("Failed to fetch text from URL.");
    }
}

// Function to display extracted text on the webpage
function displayExtractedText(text) {
    const resultContainer = document.getElementById("extractedTextContainer");
    resultContainer.innerText = text;
}

// Function to handle file uploads (PDF & TXT)
document.getElementById("fileInput").addEventListener("change", function (event) {
    const file = event.target.files[0];

    if (!file) return;

    const fileType = file.type;
    if (fileType === "application/pdf") {
        extractTextFromPDF(file);
    } else if (fileType === "text/plain") {
        extractTextFromTXT(file);
    } else {
        alert("Unsupported file type. Please upload a .pdf or .txt file.");
    }
});

// Function to send extracted text to the backend (server.js)
function sendToServer(sourceType, sourceName, extractedText) {
    fetch("/save-text", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ sourceType, sourceName, extractedText }),
    })
        .then((response) => response.json())
        .then((data) => {
            alert(data.message);
        })
        .catch((error) => {
            console.error("Error saving text:", error);
        });
}
