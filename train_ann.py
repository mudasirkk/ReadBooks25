import pandas as pd
import mysql.connector
import pickle
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Dense
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.preprocessing.sequence import pad_sequences

conn = mysql.connector.connect(
    host="cs.newpaltz.edu",
    user="p_s25_03",
    password="43n7xg",
    database="p_s25_03_db"
)

df_statements = pd.read_sql(
    "SELECT condition_if AS text, label FROM conditional_knowledge WHERE label IS NOT NULL",
    conn
)

df_questions = pd.read_sql(
    "SELECT question_text AS text, label FROM legal_questions WHERE label IS NOT NULL",
    conn
)

conn.close()

df = pd.concat([df_statements, df_questions], ignore_index=True)

texts = df['text'].astype(str).tolist()
labels = df['label'].tolist()

label_encoder = LabelEncoder()
y = label_encoder.fit_transform(labels)  # Yes → 1, No → 0

tokenizer = Tokenizer(num_words=1000, oov_token="<OOV>")
tokenizer.fit_on_texts(texts)
X = tokenizer.texts_to_sequences(texts)
X = pad_sequences(X, maxlen=20, padding='post')

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)

model = Sequential()
model.add(Dense(16, activation='relu', input_shape=(X.shape[1],)))
model.add(Dense(8, activation='relu'))
model.add(Dense(1, activation='sigmoid'))

model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])
model.fit(X_train, y_train, epochs=20, validation_data=(X_test, y_test))

model.save("legal_model.h5")

with open("tokenizer.pkl", "wb") as f:
    pickle.dump(tokenizer, f)

print("✅ Model and tokenizer saved using both conditional statements and legal questions.")
