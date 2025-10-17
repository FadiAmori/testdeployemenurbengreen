# UrbanGreen ML Service (My Version)

Personalized event recommendations for urban greening events.

## Setup
1. pip install -r requirements.txt
2. python train_model.py
3. python app.py

## API
GET /recommend/{user_id} → Top 5 events.

## Data
Generated in Colab: 5k users, 10k events, 50k interactions.