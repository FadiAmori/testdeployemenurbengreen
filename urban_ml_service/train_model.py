import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.neighbors import NearestNeighbors
from sklearn.metrics import mean_squared_error
import pickle
import os
import joblib

# Load data from your datasets folder
df = pd.read_csv('datasets/urban_events_interactions.csv')

# Fix: Drop duplicates to ensure unique user-event pairs
df = df.drop_duplicates(subset=['user_id', 'event_id'], keep='last')
print(f"Dataset shape after dropping duplicates: {df.shape}")

# Create user-event matrix (pivot ratings)
user_event_matrix = df.pivot(index='user_id', columns='event_id', values='rating').fillna(0)

# Split for validation (user-based split)
train_matrix, test_matrix = train_test_split(user_event_matrix, test_size=0.2, random_state=42)

# Train KNN model
knn = NearestNeighbors(metric='cosine', algorithm='brute', n_neighbors=10)
knn.fit(train_matrix)

# Fixed validation: Predict rated events for a test user using neighbor averages
test_user = test_matrix.iloc[0:1]  # First test user
user_vector = test_user.values.reshape(1, -1)
distances, indices = knn.kneighbors(user_vector, n_neighbors=10)

# Events the test user rated (non-zero)
rated_events_mask = test_user.iloc[0] > 0
rated_event_indices = np.where(rated_events_mask)[0]

if len(rated_event_indices) == 0:
    print("No rated events for this test user, skipping RMSE")
else:
    y_true = []
    y_pred = []
    for event_col_idx in rated_event_indices:
        event_id = user_event_matrix.columns[event_col_idx]
        # Predict: Average rating from neighbors for this event
        neighbor_ratings = [train_matrix.iloc[neigh_idx].loc[event_id] if event_id in train_matrix.iloc[neigh_idx].index else 0 for neigh_idx in indices[0]]
        pred_rating = np.mean(neighbor_ratings)
        true_rating = test_user.iloc[0, event_col_idx]
        y_true.append(true_rating)
        y_pred.append(pred_rating)
    
    rmse = np.sqrt(mean_squared_error(y_true, y_pred))
    print(f"Sample RMSE on {len(y_true)} rated events: {rmse:.2f}")

# Save model and matrix
os.makedirs('models', exist_ok=True)
with open('models/knn_recommender.pkl', 'wb') as f:
    pickle.dump(knn, f)
joblib.dump(user_event_matrix, 'models/user_event_matrix.pkl')

print("Model trained and saved to models/knn_recommender.pkl")