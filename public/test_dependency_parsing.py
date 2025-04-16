import spacy


nlp = spacy.load("en_core_web_sm") 

text = "Courts enforce property rights in legal disputes."

doc = nlp(text)

print("\n🔍 **Dependency Parsing Results:**")
for token in doc:
    print(f"Token: {token.text}, Dependency: {token.dep_}, Head: {token.head.text}, POS: {token.pos_}")
