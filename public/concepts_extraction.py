import mysql.connector
import spacy
import unicodedata
import re
import sys
import torch
from transformers import AutoTokenizer, AutoModelForTokenClassification
from nltk.corpus import stopwords
import nltk

nltk.download('stopwords')

sys.path.append("/home/khalidt2/.local/lib/python3.9/site-packages/")

nlp = spacy.load("en_core_web_sm")
tokenizer = AutoTokenizer.from_pretrained("nlpaueb/legal-bert-base-uncased")
model = AutoModelForTokenClassification.from_pretrained("nlpaueb/legal-bert-base-uncased")

db_config = {
    "host": "cs.newpaltz.edu",
    "user": "p_s25_03",
    "password": "43n7xg",
    "database": "p_s25_03_db"
}

stop_words = set(stopwords.words('english'))

def clean_text(text):
    text = unicodedata.normalize("NFKD", text)
    text = text.encode("ascii", "ignore").decode("utf-8")
    text = re.sub(r'[^a-zA-Z0-9\s]', ' ', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text.lower()

def extract_legal_concepts(text):
    inputs = tokenizer(text, return_tensors="pt", truncation=True, max_length=512)
    with torch.no_grad():
        outputs = model(**inputs)
    
    predictions = torch.argmax(outputs.logits, dim=-1).squeeze().tolist()
    tokens = tokenizer.convert_ids_to_tokens(inputs["input_ids"].squeeze().tolist())
    
    full_tokens = []
    current_word = ""

    for token, pred in zip(tokens, predictions):
        if pred != 0:
            if token.startswith("##"):
                current_word += token[2:]
            else:
                if current_word:
                    full_tokens.append(current_word)
                current_word = token
    if current_word:
        full_tokens.append(current_word)

    legal_concepts = set(full_tokens)
    return {concept for concept in legal_concepts if concept not in stop_words and len(concept) > 2}

conn = mysql.connector.connect(**db_config)
cursor = conn.cursor()

cursor.execute("SELECT id, extracted_text FROM extracted_texts WHERE processed = 0")
texts = cursor.fetchall()

for text_id, text in texts:
    text = clean_text(text)
    
    legal_concepts = extract_legal_concepts(text)
    concept_ids = {}

    for concept in legal_concepts:
        cursor.execute("SELECT c_id FROM concepts WHERE name = %s", (concept,))
        result = cursor.fetchone()
        
        if not result:
            cursor.execute("INSERT IGNORE INTO concepts (name, description) VALUES (%s, %s)", (concept, "Legal Term"))
            conn.commit()
            cursor.execute("SELECT c_id FROM concepts WHERE name = %s", (concept,))
            result = cursor.fetchone()
        
        if result:
            concept_ids[concept] = result[0]

    cursor.execute("UPDATE extracted_texts SET processed = 1 WHERE id = %s", (text_id,))
    conn.commit()

cursor.close()
conn.close()
