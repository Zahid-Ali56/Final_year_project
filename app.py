import os
import torch

# RAM Optimization (PyTorch CPU limits)
torch.set_num_threads(1)
os.environ["OMP_NUM_THREADS"] = "1"
os.environ["MKL_NUM_THREADS"] = "1"

from PIL import Image
from flask import Flask, request, jsonify
from transformers import CLIPProcessor, CLIPModel

app = Flask(__name__)

# -------------------------------------------------------------
# HYBRID MODEL LOAD LOGIC (Local Folder + Railway Cloud Safe)
# -------------------------------------------------------------
MODEL_PATH = os.path.abspath("./clip_local_model")

if os.path.exists(MODEL_PATH):
    print("Loading CLIP Model from LOCAL directory:", MODEL_PATH)
    model = CLIPModel.from_pretrained(MODEL_PATH, local_files_only=True)
    processor = CLIPProcessor.from_pretrained(MODEL_PATH, local_files_only=True)
else:
    print("Local model folder missing. Downloading lighter CLIP model from HuggingFace...")
    MODEL_NAME = "openai/clip-vit-base-patch32"
    model = CLIPModel.from_pretrained(MODEL_NAME)
    processor = CLIPProcessor.from_pretrained(MODEL_NAME)

model.eval()
print("CLIP Model Loaded Successfully!")

# -------------------------------------------------------------
# PROMPTS & KEYWORD MAP
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

# Root route
@app.route('/', methods=['GET'])
def home():
    return jsonify({'status': 'AI Service is Active', 'model_loaded': True}), 200

# Core prediction logic
def handle_prediction():
    if 'image' not in request.files:
        return jsonify({'error': 'No image uploaded'}), 400

    file = request.files['image']

    try:
        image = Image.open(file.stream).convert('RGB')
        image.thumbnail((224, 224))  # Memory optimization ke liye resize

        inputs = processor(text=PROMPTS, images=image, return_tensors="pt", padding=True)

        with torch.inference_mode():
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
        print(f"Error: {str(e)}")
        return jsonify({'error': str(e)}), 500

# Endpoint routes
@app.route('/predict-image', methods=['POST'])
def predict_image():
    return handle_prediction()

@app.route('/predict', methods=['POST'])
def predict():
    return handle_prediction()

# Server Execution
if __name__ == '__main__':
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port, debug=False)