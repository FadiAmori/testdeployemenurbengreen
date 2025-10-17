from flask import Flask, jsonify
from flask_cors import CORS  # Enables CORS
import pickle
import pandas as pd
import numpy as np
import os
import joblib
import warnings
import json  # New: For loading mapping
warnings.filterwarnings("ignore", category=UserWarning, module="sklearn")  # Quiet sklearn

app = Flask(__name__)
# Explicit CORS: Allow Laravel's origin (adjust if your port differs)
CORS(app, origins=[
    "http://127.0.0.1:8000",
    "http://localhost:8000",
    "http://127.0.0.1",
    "http://localhost"
])  # Dev-safe; prod: restrict to domain

# Load model and data
with open('models/knn_recommender.pkl', 'rb') as f:
    knn = pickle.load(f)
user_event_matrix = joblib.load('models/user_event_matrix.pkl')
df_events = pd.read_csv('datasets/urban_events_details.csv')

# New: Load ID mapping at startup
try:
    with open('models/event_id_mapping.json', 'r') as f:
        id_mapping = json.load(f)
    print(f"Loaded {len(id_mapping)} ID mappings")
except FileNotFoundError:
    print("Warning: No mapping file—using ML IDs as fallback")
    id_mapping = {}

@app.route('/recommend/<int:user_id>', methods=['GET'])
def recommend(user_id):
    if user_id not in user_event_matrix.index:
        return jsonify({'recommendations': []})  # Always JSON, no 404 body issue
    
    user_vector = user_event_matrix.loc[user_id].values.reshape(1, -1)
    distances, indices = knn.kneighbors(user_vector, n_neighbors=20)  # More neighbors for better recs
    
    user_rated = set(user_event_matrix.columns[user_vector[0] > 0])
    rec_events = []
    for idx in indices[0]:
        if len(rec_events) >= 5:
            break
        event_id_ml = user_event_matrix.columns[idx]  # e.g., 'event_9935'
        if event_id_ml not in user_rated:
            event_row = df_events[df_events['event_id'] == event_id_ml]
            if not event_row.empty:
                event = event_row.iloc[0]
                dist_idx = np.where(indices[0] == idx)[0][0]
                
                # Extract ML numeric ID from event_id (e.g., 'event_9935' -> '9935')
                ml_numeric = event_id_ml.split('_')[1] if '_' in event_id_ml else event_id_ml
                
                rec_events.append({
                    'event_id': int(ml_numeric),  # Use ML dataset ID directly
                    'title': event['title'],
                    'location': event['location'],
                    'category': event['category'],
                    'plant_step': event['plant_step'],
                    'predicted_rating': max(1, 5 - (distances[0][dist_idx] * 5)),  # Min 1 for visibility
                    'is_dataset': True  # Flag to indicate this is from ML dataset, not DB
                })
    
    return jsonify({'recommendations': rec_events})

if __name__ == '__main__':
    app.run(debug=True, port=5000)