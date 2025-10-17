from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import os
import traceback
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.neighbors import NearestNeighbors
import joblib

app = Flask(__name__)
CORS(app)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(BASE_DIR, '..', 'public', 'datasets', 'plante_notifications.csv')
MODEL_PATH = os.path.join(BASE_DIR, 'models', 'notifications')

os.makedirs(MODEL_PATH, exist_ok=True)

# French to English day mapping
DAY_MAPPING = {
    'lundi': 'monday',
    'mardi': 'tuesday',
    'mercredi': 'wednesday',
    'jeudi': 'thursday',
    'vendredi': 'friday',
    'samedi': 'saturday',
    'dimanche': 'sunday',
    'tous les jours': 'everyday'
}

def normalize_day(day_str):
    """Convert French day name to English format"""
    if not day_str or pd.isna(day_str):
        return 'everyday'
    day_lower = str(day_str).lower().strip()
    return DAY_MAPPING.get(day_lower, 'everyday')

class NotificationsModel:
    def __init__(self, dataset_path=DATA_PATH):
        self.dataset_path = dataset_path
        self.df = None
        self.vectorizer = None
        self.nn = None
        self.plant_names = None
        self._load_or_train()

    def _load_or_train(self):
        vec_file = os.path.join(MODEL_PATH, 'vectorizer_notif.joblib')
        nn_file = os.path.join(MODEL_PATH, 'nn_notif.joblib')
        data_file = os.path.join(MODEL_PATH, 'data_notif.joblib')
        
        if os.path.exists(vec_file) and os.path.exists(nn_file) and os.path.exists(data_file):
            self.vectorizer = joblib.load(vec_file)
            self.nn = joblib.load(nn_file)
            self.df = joblib.load(data_file)
            self.plant_names = self.df['plant_name'].astype(str).str.lower().unique().tolist()
            print(f"✅ Loaded notification model from cache. Plants: {len(self.plant_names)}")
            return

        # Train from CSV
        print(f"📊 Training notification model from: {self.dataset_path}")
        df = pd.read_csv(self.dataset_path)
        
        # Normalize headers
        df.columns = [c.strip().lower().replace(' ', '_') for c in df.columns]
        
        # Transform the French format to our format
        # Expected columns: nom_plante, nom_notification_1, message_notification_1, heure_notification_1, jour_notification_1, etc.
        notifications_list = []
        
        for _, row in df.iterrows():
            plant_name = str(row['nom_plante']) if 'nom_plante' in row else ''
            
            # Process notification 1
            if 'message_notification_1' in row and pd.notna(row['message_notification_1']):
                title_1 = str(row['nom_notification_1']) if 'nom_notification_1' in row and pd.notna(row['nom_notification_1']) else 'Notification'
                time_1 = str(row['heure_notification_1']) if 'heure_notification_1' in row and pd.notna(row['heure_notification_1']) else '09:00'
                day_1 = str(row['jour_notification_1']) if 'jour_notification_1' in row and pd.notna(row['jour_notification_1']) else 'everyday'
                
                notifications_list.append({
                    'plant_name': plant_name,
                    'notification_title': title_1,
                    'notification_message': str(row['message_notification_1']),
                    'days': normalize_day(day_1),  # Convert French day to English
                    'time': time_1
                })
            
            # Process notification 2
            if 'message_notification_2' in row and pd.notna(row['message_notification_2']):
                title_2 = str(row['nom_notification_2']) if 'nom_notification_2' in row and pd.notna(row['nom_notification_2']) else 'Notification'
                time_2 = str(row['heure_notification_2']) if 'heure_notification_2' in row and pd.notna(row['heure_notification_2']) else '10:00'
                day_2 = str(row['jour_notification_2']) if 'jour_notification_2' in row and pd.notna(row['jour_notification_2']) else 'everyday'
                
                notifications_list.append({
                    'plant_name': plant_name,
                    'notification_title': title_2,
                    'notification_message': str(row['message_notification_2']),
                    'days': normalize_day(day_2),  # Convert French day to English
                    'time': time_2
                })
        
        # Create DataFrame from list
        df = pd.DataFrame(notifications_list)
        
        # Deduplicate
        df = df.drop_duplicates(subset=['plant_name', 'notification_message'])
        df['plant_name_norm'] = df['plant_name'].astype(str).str.lower().str.strip()
        plant_names = df['plant_name_norm'].unique().tolist()

        # Build TFIDF on plant names
        vectorizer = TfidfVectorizer(analyzer='char_wb', ngram_range=(2,4))
        X = vectorizer.fit_transform(plant_names)
        nn = NearestNeighbors(n_neighbors=5, metric='cosine').fit(X)

        # Save
        joblib.dump(vectorizer, vec_file)
        joblib.dump(nn, nn_file)
        joblib.dump(df, data_file)

        self.vectorizer = vectorizer
        self.nn = nn
        self.df = df
        self.plant_names = plant_names
        print(f"✅ Model trained! Total plants: {len(plant_names)}, Notifications: {len(df)}")

    def recommend_notifications(self, plant_name, top_k=5):
        """
        Recommend notifications for a given plant name
        Returns list of notifications with title, message, days, and time
        """
        q = str(plant_name).lower().strip()
        
        # Exact match first
        if q in self.plant_names:
            matches = [q]
        else:
            # Find similar plants
            v = self.vectorizer.transform([q])
            dists, idxs = self.nn.kneighbors(v, n_neighbors=min(top_k, len(self.plant_names)))
            matches = [self.plant_names[i] for i in idxs[0]]
        
        # Collect notifications for matched plants
        notifications = []
        for m in matches:
            rows = self.df[self.df['plant_name_norm'] == m]
            for _, r in rows.iterrows():
                notifications.append({
                    'title': str(r['notification_title']),
                    'message': str(r['notification_message']),
                    'days': str(r['days']) if pd.notna(r['days']) else 'everyday',  # Return as string 'everyday'
                    'time': str(r['time']) if pd.notna(r['time']) else '09:00:00'
                })
        
        # Deduplicate based on title and message
        seen = set()
        out = []
        for n in notifications:
            key = (n['title'].strip().lower(), n['message'].strip().lower())
            if key in seen:
                continue
            seen.add(key)
            out.append(n)
            if len(out) >= 20:  # Limit to 20 notifications
                break
        
        return out

# Initialize model
try:
    notif_model = NotificationsModel()
except Exception as e:
    print('❌ Failed to initialize notification model:', e)
    traceback.print_exc()
    notif_model = None

@app.route('/api/generate-notifications', methods=['GET'])
def generate_notifications():
    """
    Generate notification suggestions for a plant
    Query params:
    - plant: plant name (required)
    """
    if notif_model is None:
        return jsonify({'error': 'Notification model not available'}), 500
    
    plant = request.args.get('plant')
    if not plant:
        return jsonify({'error': 'Missing plant parameter'}), 400
    
    try:
        notifications = notif_model.recommend_notifications(plant)
        if not notifications:
            return jsonify({
                'message': f'No predefined notifications found for "{plant}".',
                'notifications': []
            }), 200
        
        return jsonify({
            'plant': plant,
            'notifications': notifications,
            'count': len(notifications)
        })
    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    status = 'healthy' if notif_model is not None else 'unhealthy'
    return jsonify({
        'status': status,
        'model_loaded': notif_model is not None,
        'plants_count': len(notif_model.plant_names) if notif_model else 0
    })

if __name__ == '__main__':
    from waitress import serve
    print("🚀 Starting Notification AI Service on port 5002...")
    serve(app, host='0.0.0.0', port=5002)
