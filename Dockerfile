FROM python:3.10-slim

WORKDIR /app

# Install system dependencies needed for Git, PyTorch & OpenCV/Pillow
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

COPY . .

# 1. Install PyTorch CPU using --extra-index-url (keeps default PyPI active)
RUN pip install --no-cache-dir torch torchvision --extra-index-url https://download.pytorch.org/whl/cpu

# 2. Install normal PyPI packages
RUN pip install --no-cache-dir \
    flask \
    flask-cors \
    pillow \
    transformers \
    ftfy \
    regex \
    tqdm

# 3. Install OpenAI CLIP from GitHub
RUN pip install --no-cache-dir git+https://github.com/openai/CLIP.git

EXPOSE 5000

CMD ["python", "app.py"]
