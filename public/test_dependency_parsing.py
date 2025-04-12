import spacy

# Load spaCy model
nlp = spacy.load("en_core_web_sm")  # Change to "en_core_web_lg" if available

# Test sentence
text = "Courts enforce property rights in legal disputes."

# Process the sentence with spaCy
doc = nlp(text)

# Print token dependencies
print("\n🔍 **Dependency Parsing Results:**")
for token in doc:
    print(f"Token: {token.text}, Dependency: {token.dep_}, Head: {token.head.text}, POS: {token.pos_}")
