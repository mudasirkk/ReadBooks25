import pandas as pd
import pickle
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Dense
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.preprocessing.sequence import pad_sequences

df_conditional = pd.read_csv("conditional_knowledge.csv")  
df_questions = pd.read_csv("legal_questions.csv")           

df_conditional = df_conditional.rename(columns={'If': 'text', 'Label': 'label'})
df_questions = df_questions.rename(columns={'question_text': 'text'})

df_conditional = df_conditional[['text', 'label']]
df_questions = df_questions[['text', 'label']]

df = pd.concat([df_conditional, df_questions], ignore_index=True)

texts = df['text'].astype(str).tolist()
labels = df['label'].tolist()

label_encoder = LabelEncoder()
y = label_encoder.fit_transform(labels)  

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

print("✅ Model and tokenizer saved!")
