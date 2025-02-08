const express = require("express");
const mysql = require("mysql2");
const cors = require("cors");
const bodyParser = require("body-parser");
const multer = require("multer");
const pdfParse = require("pdf-parse");
const axios = require("axios");
const cheerio = require("cheerio"); 

const app = express();
app.use(cors({ origin: "*" })); 
app.use(bodyParser.json());

// Configure Multer for file uploads (temporary memory storage)
const storage = multer.memoryStorage();
const upload = multer({ storage: storage });

const db = mysql.createConnection({
    host: "cs.newpaltz.edu",
    user: "p_s25_03",  
    password: "43n7xg",  
    database: "p_s25_03_db", 
});

db.connect(err => {
    if (err) {
        console.error("Database connection failed: " + err.stack);
        return;
    }
    console.log("Connected to MySQL database.");
});

// ✅ API: Upload and extract text from PDF or TXT
app.post("/upload-file", upload.single("uploadedFile"), async (req, res) => {
    if (!req.file) {
        return res.status(400).json({ error: "No file uploaded." });
    }

    const fileName = req.file.originalname;
    const fileBuffer = req.file.buffer;
    let extractedText = "";

    try {
        if (fileName.endsWith(".pdf")) {
            const pdfData = await pdfParse(fileBuffer);
            extractedText = pdfData.text.trim();
        } else if (fileName.endsWith(".txt")) {
            extractedText = fileBuffer.toString("utf-8").trim();
        } else {
            return res.status(400).json({ error: "Unsupported file format." });
        }

        if (!extractedText) {
            return res.status(400).json({ error: "No text found in file." });
        }

        // Insert extracted text into MySQL
        const query = "INSERT INTO extracted_texts (source_type, source_name, extracted_text) VALUES (?, ?, ?)";
        db.query(query, ["FILE", fileName, extractedText], (err, result) => {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            res.json({ message: "Text extracted and stored successfully!", id: result.insertId });
        });

    } catch (error) {
        console.error("Error extracting text:", error);
        res.status(500).json({ error: "Failed to extract text from file." });
    }
});

// API: Extract text from a URL
app.post("/extract-url", async (req, res) => {
    const { url } = req.body;

    if (!url) {
        return res.status(400).json({ error: "No URL provided." });
    }

    try {
        const response = await axios.get(url);
        const $ = cheerio.load(response.data);

        // Extract readable text from the body
        const extractedText = $("body").text().trim();

        if (!extractedText) {
            return res.status(400).json({ error: "No text found at URL." });
        }

        // Insert extracted text into MySQL
        const query = "INSERT INTO extracted_texts (source_type, source_name, extracted_text) VALUES (?, ?, ?)";
        db.query(query, ["URL", url, extractedText], (err, result) => {
            if (err) {
                return res.status(500).json({ error: err.message });
            }
            res.json({ message: "Text extracted and stored successfully!", id: result.insertId });
        });

    } catch (error) {
        console.error("Error fetching URL:", error);
        res.status(500).json({ error: "Failed to extract text from URL." });
    }
});

// API: Retrieve stored texts
app.get("/get-texts", (req, res) => {
    db.query("SELECT * FROM extracted_texts", (err, results) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json(results);
    });
});

// Start server on port 443
app.listen(443, () => {
    console.log("Server running on port 443");
});
