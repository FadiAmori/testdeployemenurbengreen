from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import os
import traceback
import re
import joblib

app = Flask(__name__)
CORS(app)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(BASE_DIR, '..', 'public', 'datasets', 'profanity_en.csv')
MODEL_PATH = os.path.join(BASE_DIR, 'models')

os.makedirs(MODEL_PATH, exist_ok=True)

class ProfanityFilter:
    def __init__(self, dataset_path=DATA_PATH):
        self.dataset_path = dataset_path
        self.bad_words = set()
        self.patterns = []
        self._load_or_train()

    def _load_or_train(self):
        cache_file = os.path.join(MODEL_PATH, 'profanity_words.joblib')
        
        # Try to load cached bad words
        if os.path.exists(cache_file):
            cached_data = joblib.load(cache_file)
            self.bad_words = cached_data['bad_words']
            self.patterns = cached_data['patterns']
            print(f"Loaded {len(self.bad_words)} profanity words from cache")
            return

        # Load from CSV
        if not os.path.exists(self.dataset_path):
            raise FileNotFoundError(f"Profanity dataset not found at {self.dataset_path}")
        
        df = pd.read_csv(self.dataset_path)
        
        # Normalize column names
        df.columns = [c.strip().lower().replace(' ', '_') for c in df.columns]
        
        # Try to find the column with bad words (common names: word, profanity, text, bad_word)
        word_column = None
        for col in ['word', 'profanity', 'text', 'bad_word', 'term']:
            if col in df.columns:
                word_column = col
                break
        
        if word_column is None:
            # If no standard column found, use the first column
            word_column = df.columns[0]
        
        # Extract and normalize bad words
        bad_words = set()
        for word in df[word_column].dropna().astype(str):
            word = word.strip().lower()
            if word and len(word) > 1:  # Ignore single characters and empty strings
                bad_words.add(word)
                # Add variations with common substitutions
                bad_words.add(word.replace('a', '@'))
                bad_words.add(word.replace('e', '3'))
                bad_words.add(word.replace('i', '1'))
                bad_words.add(word.replace('o', '0'))
                bad_words.add(word.replace('s', '$'))
        
        # Create regex patterns for word boundary matching
        patterns = []
        for word in bad_words:
            # Escape special regex characters
            escaped = re.escape(word)
            # Create pattern that matches the word with word boundaries
            pattern = re.compile(r'\b' + escaped + r'\b', re.IGNORECASE)
            patterns.append(pattern)
        
        self.bad_words = bad_words
        self.patterns = patterns
        
        # Cache the results
        joblib.dump({
            'bad_words': self.bad_words,
            'patterns': self.patterns
        }, cache_file)
        
        print(f"Loaded {len(self.bad_words)} profanity words from dataset")

    def check_text(self, text):
        """
        Check if text contains profanity
        Returns: {
            'is_clean': bool,
            'found_words': list of detected bad words,
            'censored_text': text with bad words replaced by asterisks
        }
        """
        if not text or not isinstance(text, str):
            return {
                'is_clean': True,
                'found_words': [],
                'censored_text': text
            }
        
        text_lower = text.lower()
        found_words = []
        censored_text = text
        
        # Check for bad words using patterns
        for pattern in self.patterns:
            matches = pattern.findall(text_lower)
            if matches:
                for match in matches:
                    if match not in found_words:
                        found_words.append(match)
                    # Replace with asterisks in censored version
                    censored_text = pattern.sub('*' * len(match), censored_text)
        
        is_clean = len(found_words) == 0
        
        return {
            'is_clean': is_clean,
            'found_words': found_words,
            'censored_text': censored_text,
            'severity': 'high' if len(found_words) > 2 else ('medium' if len(found_words) > 0 else 'none')
        }

# Initialize filter
try:
    profanity_filter = ProfanityFilter()
except Exception as e:
    print('Failed to initialize profanity filter:', e)
    traceback.print_exc()
    profanity_filter = None

@app.route('/api/check-profanity', methods=['POST'])
def check_profanity():
    """
    Check text for profanity
    Request body: {
        "text": "text to check",
        "type": "statute" or "comment" (optional)
    }
    """
    if profanity_filter is None:
        return jsonify({'error': 'Profanity filter not available'}), 500
    
    data = request.get_json()
    if not data:
        return jsonify({'error': 'No JSON data provided'}), 400
    
    text = data.get('text', '')
    content_type = data.get('type', 'text')
    
    if not text:
        return jsonify({'error': 'Missing text parameter'}), 400
    
    try:
        result = profanity_filter.check_text(text)
        result['type'] = content_type
        
        return jsonify(result)
    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

@app.route('/api/check-statute', methods=['POST'])
def check_statute():
    """
    Check statute content (title + description) for profanity
    Request body: {
        "titre": "statute title",
        "description": "statute description"
    }
    """
    if profanity_filter is None:
        return jsonify({'error': 'Profanity filter not available'}), 500
    
    data = request.get_json()
    if not data:
        return jsonify({'error': 'No JSON data provided'}), 400
    
    titre = data.get('titre', '')
    description = data.get('description', '')
    
    # Check both title and description
    combined_text = f"{titre} {description}"
    
    try:
        result = profanity_filter.check_text(combined_text)
        
        # Also check individually
        title_result = profanity_filter.check_text(titre)
        desc_result = profanity_filter.check_text(description)
        
        return jsonify({
            'is_clean': result['is_clean'],
            'found_words': result['found_words'],
            'severity': result['severity'],
            'title_clean': title_result['is_clean'],
            'description_clean': desc_result['is_clean'],
            'censored_title': title_result['censored_text'],
            'censored_description': desc_result['censored_text']
        })
    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

@app.route('/api/check-comment', methods=['POST'])
def check_comment():
    """
    Check comment for profanity
    Request body: {
        "description": "comment text"
    }
    """
    if profanity_filter is None:
        return jsonify({'error': 'Profanity filter not available'}), 500
    
    data = request.get_json()
    if not data:
        return jsonify({'error': 'No JSON data provided'}), 400
    
    description = data.get('description', '')
    
    if not description:
        return jsonify({'error': 'Missing description parameter'}), 400
    
    try:
        result = profanity_filter.check_text(description)
        
        return jsonify({
            'is_clean': result['is_clean'],
            'found_words': result['found_words'],
            'censored_text': result['censored_text'],
            'severity': result['severity']
        })
    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

@app.route('/api/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'filter_loaded': profanity_filter is not None,
        'words_count': len(profanity_filter.bad_words) if profanity_filter else 0
    })

if __name__ == '__main__':
    from waitress import serve
    print("Starting Profanity Filter API on http://0.0.0.0:50099")
    serve(app, host='0.0.0.0', port=50099)
