from flask import Flask, request, jsonify
from flask_cors import CORS
import google.generativeai as genai
import os
import pandas as pd
import traceback

app = Flask(__name__)
CORS(app)

# Configuration de Gemini
GEMINI_API_KEY = os.getenv('GEMINI_API_KEY', 'AIzaSyCYC_lt-MeE8jA9qNs01b3uzL_vnkacSi0')
genai.configure(api_key=GEMINI_API_KEY)

# Configuration du modèle
generation_config = {
    "temperature": 0.7,
    "top_p": 0.95,
    "top_k": 40,
    "max_output_tokens": 1024,
}

safety_settings = [
    {"category": "HARM_CATEGORY_HARASSMENT", "threshold": "BLOCK_MEDIUM_AND_ABOVE"},
    {"category": "HARM_CATEGORY_HATE_SPEECH", "threshold": "BLOCK_MEDIUM_AND_ABOVE"},
    {"category": "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold": "BLOCK_MEDIUM_AND_ABOVE"},
    {"category": "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold": "BLOCK_MEDIUM_AND_ABOVE"},
]

# Initialiser le modèle Gemini
try:
    model = genai.GenerativeModel(
        model_name='gemini-1.5-flash',
        generation_config=generation_config,
        safety_settings=safety_settings
    )
    print("✅ Gemini AI model initialized successfully")
except Exception as e:
    print(f"❌ Failed to initialize Gemini AI: {e}")
    model = None

# Charger les données des plantes pour enrichir le contexte
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(BASE_DIR, '..', 'public', 'datasets', 'plante_notifications.csv')

plant_knowledge = ""
try:
    df = pd.read_csv(DATA_PATH)
    df.columns = [c.strip().lower().replace(' ', '_') for c in df.columns]
    
    # Construire une base de connaissances à partir du CSV
    knowledge_items = []
    for _, row in df.iterrows():
        plant_name = str(row.get('nom_plante', ''))
        if plant_name and plant_name != 'nan':
            knowledge_items.append(f"- {plant_name}")
            
            if 'nom_notification_1' in row and pd.notna(row['nom_notification_1']):
                knowledge_items.append(f"  • {row['nom_notification_1']}: {row.get('message_notification_1', '')}")
            
            if 'nom_notification_2' in row and pd.notna(row['nom_notification_2']):
                knowledge_items.append(f"  • {row['nom_notification_2']}: {row.get('message_notification_2', '')}")
    
    plant_knowledge = "\n".join(knowledge_items[:100])  # Limiter pour ne pas dépasser le contexte
    print(f"✅ Loaded plant knowledge from database ({len(knowledge_items)} items)")
    
except Exception as e:
    print(f"⚠️ Could not load plant data: {e}")
    plant_knowledge = "Base de données non disponible"

# Système de prompt pour Gemini
SYSTEM_PROMPT = f"""Tu es un expert en jardinage et entretien des plantes pour UrbanGreen, une plateforme de vente de plantes en ligne.

**Ton rôle :**
- Répondre aux questions sur l'entretien des plantes (arrosage, lumière, fertilisation, taille, maladies, etc.)
- Donner des conseils pratiques et précis
- Être amical, professionnel et encourageant
- Répondre en français

**Connaissances sur nos plantes :**
{plant_knowledge}

**Instructions :**
1. Réponds de manière concise et structurée
2. Utilise des emojis pertinents (💧 pour eau, ☀️ pour lumière, 🌱 pour fertilisation, etc.)
3. Si tu ne sais pas, dis-le honnêtement et propose des alternatives
4. Adapte tes conseils selon la plante mentionnée
5. Sois positif et encourageant

**Format de réponse :**
- Commence par saluer l'utilisateur si c'est la première question
- Structure ta réponse avec des points ou des sections
- Termine avec un conseil ou encouragement
"""

@app.route('/api/chat', methods=['POST'])
def chat():
    """
    Chat endpoint utilisant Gemini AI
    Body: { "question": "Comment arroser ma tomate?", "history": [] }
    """
    if model is None:
        return jsonify({'error': 'Gemini AI not available'}), 500
    
    data = request.get_json()
    question = data.get('question', '').strip()
    history = data.get('history', [])
    
    if not question:
        return jsonify({'error': 'Question is required'}), 400
    
    try:
        # Construire le contexte avec l'historique
        chat_session = model.start_chat(history=[])
        
        # Ajouter le prompt système et la question
        full_prompt = f"{SYSTEM_PROMPT}\n\nQuestion de l'utilisateur : {question}\n\nRéponds de manière concise et utile :"
        
        # Générer la réponse
        response = chat_session.send_message(full_prompt)
        answer = response.text
        
        # Calculer un score de confiance basé sur la longueur de la réponse
        confidence = min(len(answer) / 500.0, 1.0)
        
        # Extraire le nom de la plante si mentionné
        plant_name = extract_plant_name(question.lower())
        
        return jsonify({
            'success': True,
            'answer': answer,
            'confidence': confidence,
            'plant': plant_name,
            'sources': [{'type': 'gemini-ai', 'model': 'gemini-1.5-flash'}]
        })
        
    except Exception as e:
        traceback.print_exc()
        return jsonify({
            'error': 'Failed to generate response',
            'details': str(e)
        }), 500

def extract_plant_name(question):
    """Extraire le nom de la plante de la question"""
    common_plants = [
        'tomate', 'basilic', 'menthe', 'persil', 'coriandre',
        'thym', 'romarin', 'lavande', 'rose', 'orchidée',
        'cactus', 'succulente', 'ficus', 'monstera', 'pothos'
    ]
    
    for plant in common_plants:
        if plant in question.lower():
            return plant.title()
    
    return None

@app.route('/api/chat/suggestions', methods=['GET'])
def suggestions():
    """Obtenir des suggestions de questions"""
    suggestions = [
        {
            'category': 'Arrosage',
            'icon': '💧',
            'questions': [
                'Comment arroser ma tomate ?',
                'À quelle fréquence arroser mes plantes ?',
                'Comment savoir si ma plante a besoin d\'eau ?',
                'Quelle quantité d\'eau pour mes plantes ?'
            ]
        },
        {
            'category': 'Lumière',
            'icon': '☀️',
            'questions': [
                'Quelle exposition pour ma plante ?',
                'Ma plante peut-elle supporter le soleil direct ?',
                'Comment améliorer la lumière pour mes plantes ?',
                'Combien d\'heures de lumière par jour ?'
            ]
        },
        {
            'category': 'Fertilisation',
            'icon': '🌱',
            'questions': [
                'Quand fertiliser mes tomates ?',
                'Quel engrais utiliser pour mes plantes ?',
                'Comment nourrir mes plantes en hiver ?',
                'À quelle fréquence fertiliser ?'
            ]
        },
        {
            'category': 'Taille',
            'icon': '✂️',
            'questions': [
                'Comment tailler ma plante ?',
                'Quand couper les feuilles mortes ?',
                'Comment favoriser la croissance par la taille ?',
                'Dois-je tailler mes plantes en hiver ?'
            ]
        },
        {
            'category': 'Problèmes',
            'icon': '🩺',
            'questions': [
                'Pourquoi mes feuilles jaunissent ?',
                'Comment traiter les parasites ?',
                'Ma plante perd ses feuilles, que faire ?',
                'Comment prévenir les maladies ?'
            ]
        },
        {
            'category': 'Entretien général',
            'icon': '🌿',
            'questions': [
                'Comment prendre soin de mes plantes ?',
                'Quelle température pour mes plantes d\'intérieur ?',
                'Comment augmenter l\'humidité ?',
                'Quand rempoter mes plantes ?'
            ]
        }
    ]
    
    return jsonify({
        'success': True,
        'suggestions': suggestions
    })

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy' if model is not None else 'unhealthy',
        'ai_model': 'gemini-1.5-flash',
        'model_loaded': model is not None,
        'api_configured': GEMINI_API_KEY != ''
    })

if __name__ == '__main__':
    from waitress import serve
    print("🤖 Starting Gemini AI Chat Service on port 5003...")
    print(f"📡 API Key configured: {GEMINI_API_KEY[:20]}..." if GEMINI_API_KEY else "❌ No API key")
    serve(app, host='0.0.0.0', port=5003)
