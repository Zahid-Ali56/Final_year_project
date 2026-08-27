import os
import torch                 #Neural network ki heavy calculations/maths

from PIL import Image        #user ki taraf se aane wali image files ko open krna 
from flask import Flask, request, jsonify
from transformers import CLIPProcessor, CLIPModel    #images & text/prompts Ai ko samajhne ke liye hi

# -------------------------------------------------------------
# 1. HUGGING FACE OFFLINE SETTINGS (INTERNET CHECKS KO BLOCK KARNA)
# -------------------------------------------------------------
# In lines se Hugging Face library ko instruction milti hai ke local host files use kare.
os.environ["TRANSFORMERS_OFFLINE"] = "1"
os.environ["HF_HUB_OFFLINE"] = "1"

# Flask Application initialize kar rahe hain
app = Flask(__name__)

# -------------------------------------------------------------
# 2. LOCAL OFFLINE MODEL LOAD KARNA (UPDATED FOR ABSOLUTE PATH)
# -------------------------------------------------------------
MODEL_PATH = os.path.abspath("./clip_local_model")

print("Checking Model Directory:", MODEL_PATH)

# Agar local folder create nahi hua toh user ko clear message show hoga
if not os.path.exists(MODEL_PATH):
    raise FileNotFoundError(
        f"Folder '{MODEL_PATH}' nahi mila! Pehle download script chala kar model save karein."
    )

print("CLIP Model ko local folder se load kiya ja raha hai...")
# local_files_only=True se library ko strictly bataya jata hai ke internet par contact na kare
model = CLIPModel.from_pretrained(MODEL_PATH, local_files_only=True)
processor = CLIPProcessor.from_pretrained(MODEL_PATH, local_files_only=True)
print("Model bina internet ke successfully load ho gaya!")

# -------------------------------------------------------------
# 3. AI DESCRIPTIVE PROMPTS (CATEGORY SENTENCES)
# -------------------------------------------------------------
# CLIP model aik sentence ko behter samajhta hai, is liye hum har product ke descriptive sentences de rahe hain
PROMPTS = [
    "a photo of a shirt, t-shirt, or top clothing",
    "a photo of a digital camera, DSLR, or camera lens",
    "a photo of a laptop, computer, or notebook",
    "a photo of pants, jeans, or trousers",
    "a photo of shoes, sneakers, or footwear",
    "a photo of a wristwatch, smart watch, or clock",
    "a photo of a jacket, coat, or blazer",
    "a photo of a dress, frock, or skirt"
]

# -------------------------------------------------------------
# 4. SENTENCE TO DATABASE TAG MAPPING
# -------------------------------------------------------------
# Jab AI sentence ko detect karega, toh hum usko MySQL Database ke simple single-word tag me convert karenge
KEYWORD_MAP = {
    "a photo of a shirt, t-shirt, or top clothing": "shirt",
    "a photo of a digital camera, DSLR, or camera lens": "camera",
    "a photo of a laptop, computer, or notebook": "laptop",
    "a photo of pants, jeans, or trousers": "pants",
    "a photo of shoes, sneakers, or footwear": "shoes",
    "a photo of a wristwatch, smart watch, or clock": "watch",
    "a photo of a jacket, coat, or blazer": "jacket",
    "a photo of a dress, frock, or skirt": "dress"
}

# -------------------------------------------------------------
# 5. API ENDPOINT FOR IMAGE PREDICTION
# -------------------------------------------------------------
@app.route('/predict-image', methods=['POST'])
def predict_image():
    # Verification: Check kar rahe hain ke PHP ne image upload ki hai ya nahi
    if 'image' not in request.files:
        return jsonify({'error': 'No image uploaded'}), 400

    file = request.files['image']

    try:
        # Step A: Image ko open karke RGB channels me convert kar rahe hain
        image = Image.open(file.stream).convert('RGB')

        # Step B: Image aur Humare Prompts ko CLIP AI ke samajhne k qabil (Tensors) banaya ja raha hai
        inputs = processor(text=PROMPTS, images=image, return_tensors="pt", padding=True)

        # Step C: Model Prediction (torch.no_grad se processing fast hoti hai aur memory bachti hai)
        with torch.no_grad():
            outputs = model(**inputs)

        # Step D: Raw Model Outputs ko Percentage / Probabilities me convert kar rahe hain (Softmax Algorithm)
        logits_per_image = outputs.logits_per_image 
        probs = logits_per_image.softmax(dim=1)

        # Step E: Sab se highest percentage wale prompt ko dhoond rahe hain
        best_match_idx = probs.argmax().item()
        matched_prompt = PROMPTS[best_match_idx]
        confidence = float(probs[0][best_match_idx]) * 100

        # Step F: Confidence Check (Agar result 40% se kaam hoga toh "unknown" tag return karenge)
        if confidence < 40.0:
            detected_category = "unknown"
        else:
            detected_category = KEYWORD_MAP[matched_prompt]

        # Step G: Result ko JSON Format me PHP Application ko bhejna
        return jsonify({
            'category': detected_category,
            'confidence': round(confidence, 2)
        })

    except Exception as e:
        # Emergency Check: Agar koi error aaye toh exception catch karke return karenge
        return jsonify({'error': str(e)}), 500

# -------------------------------------------------------------
# 6. LOCAL SERVER EXECUTION
# -------------------------------------------------------------
if __name__ == '__main__':
    # Microservice ko Port 5000 par local host me start kar rahe hain
    app.run(port=5000)