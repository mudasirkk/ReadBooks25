import pandas as pd
import mysql.connector

conn = mysql.connector.connect(
    host="cs.newpaltz.edu",
    user="p_s25_03",
    password="43n7xg",
    database="p_s25_03_db"
)

df = pd.read_sql("SELECT id, consequence_then FROM conditional_knowledge", conn)

positive_keywords = [
    'transfer', 'gain', 'receive', 'valid', 'enforceable', 'effective',
    'create', 'trigger', 'obtain', 'result in', 'establish', 'permit'
]

negative_keywords = [
    'not transfer', 'invalid', 'unenforceable', 'no effect', 'fail',
    'reject', 'prevent', 'stop', 'bar', 'void'
]

def auto_label(text):
    text = text.lower()
    if any(kw in text for kw in positive_keywords):
        return 'Yes'
    elif any(kw in text for kw in negative_keywords):
        return 'No'
    else:
        return 'unknown'  # Default label
    
df['label'] = df['consequence_then'].apply(auto_label)

cursor = conn.cursor()
for i, row in df.iterrows():
    cursor.execute(
        "UPDATE conditional_knowledge SET label = %s WHERE id = %s",
        (row['label'], int(row['id']))
    )

conn.commit()
cursor.close()
conn.close()

print("✅ Labels successfully generated and stored.")
