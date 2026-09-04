import os
import torch                 # Neural network ki heavy calculations/maths

from PIL import Image        # user ki taraf se aane wali image files ko open krna 
from flask import Flask, request, jsonify
from transformers import CLIPProcessor, CLIPModel    # images & text/prompts Ai ko samajhne ke liye hi

# -------------------------------------------------------------
# 1. HUGGING FACE OFFLINE SETTINGS
# -------------------------------------------------------------
os.environ["TRANSFORMERS_OFFLINE"] = "1"
os.environ["HF_HUB_OFFLINE"] = "1"

app = Flask(__name__)

# -------------------------------------------------------------
# 2. LOCAL OFFLINE MODEL LOAD KARNA
# -------------------------------------------------------------
MODEL_PATH = os.path.abspath("./clip_local_model")

print("Checking Model Directory:", MODEL_PATH)

if not os.path.exists(MODEL_PATH):
    raise FileNotFoundError(
        f"Folder '{MODEL_PATH}' nahi mila! Pehle download script chala kar model save karein."
    )

print("CLIP Model ko local folder se load kiya ja raha hai...")
model = CLIPModel.from_pretrained(MODEL_PATH, local_files_only=True)
processor = CLIPProcessor.from_pretrained(MODEL_PATH, local_files_only=True)
print("Model bina internet ke successfully load ho gaya!")

# -------------------------------------------------------------
# 3. PROMPTS & KEYWORD MAP
# -------------------------------------------------------------
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

# Root route taake 404 error na aaye jab base domain check ho
@app.route('/', methods=['GET'])
def home():
    return jsonify({'status': 'AI Service is Active'}), 200

# Core prediction logic
def handle_prediction():
    if 'image' not in request.files:
        return jsonify({'error': 'No image uploaded'}), 400

    file = request.files['image']

    try:
        image = Image.open(file.stream).convert('RGB')
        inputs = processor(text=PROMPTS, images=image, return_tensors="pt", padding=True)

        with torch.no_grad():
            outputs = model(**inputs)

        logits_per_image = outputs.logits_per_image 
        probs = logits_per_image.softmax(dim=1)

        best_match_idx = probs.argmax().item()
        matched_prompt = PROMPTS[best_match_idx]
        confidence = float(probs[0][best_match_idx]) * 100

        if confidence < 40.0:
            detected_category = "unknown"
        else:
            detected_category = KEYWORD_MAP[matched_prompt]

        return jsonify({
            'category': detected_category,
            'confidence': round(confidence, 2)
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

# Both endpoints support
@app.route('/predict-image', methods=['POST'])
def predict_image():
    return handle_prediction()

@app.route('/predict', methods=['POST'])
def predict():
    return handle_prediction()

# -------------------------------------------------------------
# 6. LOCAL SERVER EXECUTION (Host set to 0.0.0.0 for Ngrok)
# -------------------------------------------------------------
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)