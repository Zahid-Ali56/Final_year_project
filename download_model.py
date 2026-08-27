import os
from transformers import CLIPModel, CLIPProcessor

# System ka exact full absolute path nikalna
folder_path = os.path.join(os.getcwd(), "clip_local_model")

print("Model Hugging Face se download ho raha hai...")
model = CLIPModel.from_pretrained("openai/clip-vit-base-patch32")
processor = CLIPProcessor.from_pretrained("openai/clip-vit-base-patch32")

# Local folder me exact save karna
model.save_pretrained(folder_path)
processor.save_pretrained(folder_path)

print(f"SUCCESS: Model completely save ho gaya is location par: {folder_path}")