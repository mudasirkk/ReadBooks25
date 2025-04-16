import sys
import json
import pickle
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.sequence import pad_sequences

model = load_model("legal_model.h5")
with open("tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

question = sys.argv[1]

seq = tokenizer.texts_to_sequences([question])
padded = pad_sequences(seq, maxlen=20, padding='post')
pred = model.predict(padded)[0][0]
answer = "Yes" if pred >= 0.5 else "No"

print(json.dumps({"answer": answer}))
