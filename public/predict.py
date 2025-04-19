import sys
import json
import pickle
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.sequence import pad_sequences

model = load_model("/var/projects/s25-03/html/v1/public/legal_model.h5", compile=False)

with open("/var/projects/s25-03/html/v1/public/tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

rules = pd.read_csv("/var/projects/s25-03/html/v1/public/conditional_knowledge.csv")
rules = rules.dropna(subset=['If', 'Then'])

question = sys.argv[1]

seq = tokenizer.texts_to_sequences([question])
padded = pad_sequences(seq, maxlen=20, padding='post')
pred = model.predict(padded)[0][0]
answer = "Yes" if pred >= 0.5 else "No"
confidence = pred * 100 if pred >= 0.5 else (1 - pred) * 100

tfidf = TfidfVectorizer()
tfidf_matrix = tfidf.fit_transform(rules['If'].astype(str))
question_vec = tfidf.transform([question])
similarities = cosine_similarity(question_vec, tfidf_matrix)[0]
best_match_index = similarities.argmax()
best_if = rules.iloc[best_match_index]['If']
best_then = rules.iloc[best_match_index]['Then']
best_rule = f"If {best_if} then {best_then}".strip()

if not best_rule.endswith('.'):
    best_rule += '.'

print(json.dumps({
    "answer": answer,
    "confidence": f"{confidence:.1f}%",
    "rule": best_rule
}))
