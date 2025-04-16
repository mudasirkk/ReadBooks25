from flask import Flask, request, jsonify
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.sequence import pad_sequences
import pickle

with open("tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

model = load_model("legal_model.h5")

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    input_text = data['text']
    
    sequence = tokenizer.texts_to_sequences([input_text])
    padded = pad_sequences(sequence, maxlen=20, padding='post')
    prediction = model.predict(padded)[0][0]
    
    result = "Yes" if prediction >= 0.5 else "No"
    return jsonify({"input": input_text, "answer": result})

if __name__ == '__main__':
    app.run(debug=True)
