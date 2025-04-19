from flask import Flask, request, jsonify
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.sequence import pad_sequences
import pickle

app = Flask(__name__)

model = load_model("/var/projects/s25-03/html/v1/public/legal_model.h5")
with open("/var/projects/s25-03/html/v1/public/tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    text = data.get("text", "")

    sequence = tokenizer.texts_to_sequences([text])
    padded = pad_sequences(sequence, maxlen=20)
    prediction = model.predict(padded)[0][0]

    answer = "Yes" if prediction >= 0.5 else "No"
    return jsonify({"answer": answer})

if __name__ == "__main__":
    app.run(port=5000)
