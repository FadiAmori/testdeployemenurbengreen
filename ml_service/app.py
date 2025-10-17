from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import os
import traceback
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.neighbors import NearestNeighbors
import joblib
try:
    import requests  # Optional; we fallback to urllib if missing
except Exception:
    requests = None
import json

# Try to load Laravel .env so AI_API_KEY is visible when launching python directly
try:
    from dotenv import load_dotenv
    _ENV_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '.env')
    if os.path.exists(_ENV_PATH):
        load_dotenv(_ENV_PATH)
except Exception:
    # Fallback manual parse for AI_API_KEY if python-dotenv is not installed
    _ENV_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '.env')
    try:
        if not os.environ.get('AI_API_KEY') and os.path.exists(_ENV_PATH):
            with open(_ENV_PATH, 'r') as _f:
                for _line in _f:
                    if _line.strip().startswith('AI_API_KEY'):
                        _val = _line.split('=', 1)[1].strip().strip('"').strip("'")
                        os.environ['AI_API_KEY'] = _val
                        break
    except Exception:
        pass

app = Flask(__name__)
CORS(app)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(BASE_DIR, '..', 'public', 'datasets', 'plant_steps_dataset.csv')
MODEL_PATH = os.path.join(BASE_DIR, 'models')

os.makedirs(MODEL_PATH, exist_ok=True)

class StepsModel:
    def __init__(self, dataset_path=DATA_PATH):
        self.dataset_path = dataset_path
        self.df = None
        self.vectorizer = None
        self.nn = None
        self.plant_names = None
        self._load_or_train()

    def _load_or_train(self):
        vec_file = os.path.join(MODEL_PATH, 'vectorizer.joblib')
        nn_file = os.path.join(MODEL_PATH, 'nn.joblib')
        data_file = os.path.join(MODEL_PATH, 'data.joblib')
        if os.path.exists(vec_file) and os.path.exists(nn_file) and os.path.exists(data_file):
            self.vectorizer = joblib.load(vec_file)
            self.nn = joblib.load(nn_file)
            self.df = joblib.load(data_file)
            self.plant_names = self.df['plant_name'].astype(str).str.lower().unique().tolist()
            return

        # Train from CSV
        df = pd.read_csv(self.dataset_path)
        # Normalize headers
        df.columns = [c.strip().lower().replace(' ', '_') for c in df.columns]
        # Required cols: plant_name, step_title, step_description
        required = ['plant_name', 'step_title', 'step_description']
        for r in required:
            if r not in df.columns:
                raise FileNotFoundError(f"Required column missing: {r}")
        # Deduplicate
        df = df.drop_duplicates(subset=['plant_name', 'step_title', 'step_description'])
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

    def recommend_steps(self, plant_name, top_k=5):
        q = str(plant_name).lower().strip()
        if q in self.plant_names:
            matches = [q]
        else:
            v = self.vectorizer.transform([q])
            dists, idxs = self.nn.kneighbors(v, n_neighbors=min(top_k, len(self.plant_names)))
            matches = [self.plant_names[i] for i in idxs[0]]
        # Collect steps for matches
        steps = []
        for m in matches:
            rows = self.df[self.df['plant_name_norm'] == m]
            for _, r in rows.iterrows():
                steps.append({
                    'title': str(r['step_title']),
                    'description': str(r['step_description'])
                })
        # Deduplicate
        seen = set()
        out = []
        for s in steps:
            key = (s['title'].strip().lower(), s['description'].strip().lower())
            if key in seen:
                continue
            seen.add(key)
            out.append(s)
            if len(out) >= 30:
                break
        return out

# Initialize model
try:
    model = StepsModel()
except Exception as e:
    print('Failed to initialize model:', e)
    traceback.print_exc()
    model = None

@app.route('/api/generate-steps', methods=['GET'])
def generate_steps():
    if model is None:
        return jsonify({'error': 'Model not available'}), 500
    plant = request.args.get('plant')
    if not plant:
        return jsonify({'error': 'Missing plant parameter'}), 400
    try:
        steps = model.recommend_steps(plant)
        if not steps:
            return jsonify({'message': 'No predefined steps found for this plant.'}), 404
        return jsonify(steps)
    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

# ------------------------
# Gemini-backed endpoints
# ------------------------

GEMINI_MODEL = os.environ.get('GEMINI_MODEL', 'gemini-1.5-flash')
GEMINI_API_KEY = os.environ.get('AI_API_KEY')
GEMINI_BASES = [
    'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
    'https://generativelanguage.googleapis.com/v1/models/{model}:generateContent',
]


def _gemini_generate(prompt: str, mime: str | None = 'application/json', temperature: float = 0.3, system: str | None = None):
    if not GEMINI_API_KEY:
        return None, 'Missing AI_API_KEY environment variable'
    try:
        payload = {
            "contents": [{"role": "user", "parts": [{"text": prompt}]}],
            "generationConfig": {"temperature": temperature},
        }
        if system:
            payload["systemInstruction"] = {"parts": [{"text": system}]}
        # Gemini expects camelCase responseMimeType
        if mime:
            # Send both keys for compatibility across Gemini API variants
            response_mime = mime
            payload["generationConfig"]["responseMimeType"] = response_mime
            payload["generationConfig"]["response_mime_type"] = response_mime
        # Try multiple model ids and API versions to avoid 404s
        candidates = []
        seen = set()
        seeds = [GEMINI_MODEL, f"{GEMINI_MODEL}-latest", 'gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-pro']
        for m in seeds:
            m = (m or '').strip()
            if not m or m in seen:
                continue
            seen.add(m)
            candidates.append(m)

        last_err = None
        for model in candidates:
            for base in GEMINI_BASES:
                url = base.format(model=model)
                try:
                    if requests is not None:
                        r = requests.post(url, params={"key": GEMINI_API_KEY}, json=payload, timeout=45)
                        if r.status_code >= 400:
                            try:
                                j = r.json(); msg = (j.get('error') or {}).get('message') or str(j)
                            except Exception:
                                msg = r.text
                            last_err = f"HTTP {r.status_code} on {model}: {msg}"
                            continue
                        data = r.json()
                    else:
                        import urllib.request, urllib.parse
                        full_url = url + '?' + urllib.parse.urlencode({"key": GEMINI_API_KEY})
                        req = urllib.request.Request(full_url, data=json.dumps(payload).encode('utf-8'), headers={'Content-Type': 'application/json'})
                        with urllib.request.urlopen(req, timeout=45) as resp:
                            data = json.loads(resp.read().decode('utf-8'))

                    # Parse candidate text
                    cand = (data.get('candidates') or [{}])[0]
                    content = (cand.get('content') or {})
                    parts = content.get('parts') or []
                    text = parts[0].get('text') if parts else ''
                    return text, None
                except Exception as e:
                    last_err = str(e)
                    continue

        return None, (last_err or 'Unknown error contacting Gemini')
        # Gemini responds with candidates -> content.parts[0].text
        cand = (data.get('candidates') or [{}])[0]
        content = (cand.get('content') or {})
        parts = content.get('parts') or []
        text = parts[0].get('text') if parts else ''
        return text, None
    except Exception as e:
        return None, str(e)


@app.post('/nl-order')
def nlp_order():
    try:
        body = request.get_json(force=True, silent=True) or {}
        system_prompt = body.get('system_prompt', '')
        examples = body.get('examples', [])
        json_schema = body.get('json_schema', {})
        catalog = body.get('catalog', {})
        user_message = body.get('user_message', '')

        # Construct a robust prompt including schema, catalog and examples
        prompt = (
            f"Catalogue (extraits):\n{json.dumps(catalog)[:8000]}\n\n"
            f"Schéma JSON strict à respecter:\n{json.dumps(json_schema)}\n\n"
            "Exemples (few-shot):\n" + json.dumps(examples)[:4000] + "\n\n"
            f"Requête utilisateur:\n{user_message}\n\n"
            "Réponds UNIQUEMENT en JSON valide (sans texte autour)."
        )

        text, err = _gemini_generate(prompt, mime='application/json', system=system_prompt)
        if not err:
            try:
                parsed = json.loads(text)
                return jsonify(parsed), 200
            except Exception:
                err = 'Réponse non JSON du modèle'

        # Fallback local parsing if Gemini fails
        parsed = _fallback_cart_parse(user_message, catalog)
        if parsed['items']:
            return jsonify(parsed), 200
        return jsonify({"items": [], "confidence": 0.0, "clarification": f"Erreur IA: {err}"}), 200
    except Exception as e:
        traceback.print_exc()
        return jsonify({"items": [], "confidence": 0.0, "clarification": str(e)}), 200


@app.post('/admin/report')
def admin_report():
    try:
        body = request.get_json(force=True, silent=True) or {}
        system_prompt = body.get('system_prompt', '')
        metrics = body.get('metrics', {})
        period_start = body.get('period_start')
        period_end = body.get('period_end')

        # Reduce payload to avoid 400 due to oversized prompts
        def _slim(m: dict) -> dict:
            out = {}
            kpis = m.get('kpis') or {}
            out['kpis'] = kpis
            ts = m.get('time_series') or []
            out['time_series'] = ts[-14:]
            pi = m.get('product_insights') or {}
            out['product_insights'] = {
                'top_products': (pi.get('top_products') or [])[:10],
                'decliners': (pi.get('decliners') or [])[:10]
            }
            return out

        metrics_slim = _slim(metrics)
        prompt = (
            f"Période: {period_start} → {period_end}\n\n"
            f"Métriques fournies (ne pas inventer d'autres données):\n{json.dumps(metrics_slim)[:9000]}\n\n"
            "Réponds en Markdown en respectant strictement la structure demandée."
        )

        # Avoid responseMimeType for markdown to reduce 400 risk
        text, err = _gemini_generate(prompt, mime=None, system=system_prompt)
        if not err and text:
            return jsonify({"markdown": text}), 200

        # Fallback simple markdown from metrics
        return jsonify({"markdown": _fallback_report_markdown(metrics, period_start, period_end, err)}), 200
    except Exception as e:
        traceback.print_exc()
        return jsonify({"markdown": f"Erreur: {str(e)}"}), 200


# ------------------------
# Charts helpers for Admin UI
# ------------------------
@app.post('/admin/report/charts')
def report_charts():
    try:
        body = request.get_json(force=True, silent=True) or {}
        metrics = body.get('metrics') or {}
        kpis = metrics.get('kpis') or {}
        time_series = metrics.get('time_series') or []
        product_insights = metrics.get('product_insights') or {}
        top_products = product_insights.get('top_products') or []

        # Prepare time-series
        labels = [str(p.get('date')) for p in time_series]
        revenue = [float(p.get('revenue') or 0) for p in time_series]
        orders = [int(p.get('orders') or 0) for p in time_series]

        # Prepare top products bar series
        tp_labels = [str(p.get('name') or p.get('sku'))[:22] for p in top_products]
        tp_values = [float(p.get('revenue') or 0) for p in top_products]

        # KPI summary (flatten)
        def _kpi_row(name):
            v = kpis.get(name) or {}
            return {
                'current': float(v.get('current') or 0),
                'previous': float(v.get('previous') or 0),
                'delta_pct': float(v.get('delta_pct') or 0) if v.get('delta_pct') is not None else None,
            }

        out = {
            'timeseries': {
                'labels': labels,
                'revenue': revenue,
                'orders': orders,
            },
            'top_products': {
                'labels': tp_labels,
                'revenue': tp_values,
            },
            'kpis': {
                'sales': _kpi_row('sales'),
                'orders': _kpi_row('orders'),
                'aov': _kpi_row('aov'),
                'conversion_rate': _kpi_row('conversion_rate'),
                'avg_items_per_order': _kpi_row('avg_items_per_order'),
            }
        }

        return jsonify(out), 200
    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': str(e)}), 200

# ------------------------
# Local fallbacks
# ------------------------

def _best_catalog_match(token: str, products: list[dict]) -> dict | None:
    token_l = token.strip().lower()
    if not token_l:
        return None
    # Normalize
    import re
    def norm(s: str) -> str:
        return re.sub(r'[^a-z0-9]+', '', s.lower())

    token_n = norm(token_l)
    best = None
    best_score = 0
    for p in products:
        sku = str(p.get('sku', '')).strip()
        name = str(p.get('name', '')).strip()
        sku_n = norm(sku)
        name_n = norm(name)
        score = 0
        if token_n and sku_n == token_n:
            score = 100
        elif token_n and name_n.find(token_n) != -1:
            score = len(token_n)
        elif token_l and name.lower().find(token_l) != -1:
            score = len(token_l)
        if score > best_score:
            best_score = score
            best = p
    return best if best_score > 0 else None


def _fallback_cart_parse(utterance: str, catalog: dict) -> dict:
    products = catalog.get('products') or []
    import re
    items = []
    for m in re.finditer(r'(\d{1,3})\s*(?:x|×)?\s*([\w\-]{2,})', utterance, flags=re.I):
        qty = max(1, int(m.group(1)))
        token = m.group(2)
        best = _best_catalog_match(token, products)
        if best:
            items.append({
                'sku_or_name': best.get('sku') or best.get('name'),
                'qty': qty,
                'variant': {},
            })
    confidence = 0.85 if items else 0.3
    return {
        'items': items,
        'confidence': confidence,
    }


def _fallback_report_markdown(metrics: dict, start: str, end: str, err: str | None) -> str:
    sales = ((metrics or {}).get('kpis') or {}).get('sales') or {}
    orders = ((metrics or {}).get('kpis') or {}).get('orders') or {}
    sales_c = sales.get('current', 0)
    orders_c = orders.get('current', 0)
    md = [f"# Rapport IA – Période {start} → {end}"]
    md.extend([
        "\n## Résumé exécutif (≤120 mots)",
        f"Ventes: {sales_c} — Commandes: {orders_c}.",
        "\n## KPI (table N vs N-1) + variation %",
        "| KPI | N | N-1 | Variation |\n|---|---:|---:|---:|",
        f"| Ventes | {sales.get('current', 0)} | {sales.get('previous', 0)} | {sales.get('delta_pct') or 0}% |",
        f"| Commandes | {orders.get('current', 0)} | {orders.get('previous', 0)} | {orders.get('delta_pct') or 0}% |",
        "\n## Ce qui fonctionne (bullet points)\n- Top ventes stables.",
        "\n## Ce qui ne fonctionne pas (bullet points)\n- Produits en baisse à analyser.",
        "\n## Analyse & causes probables\n- Variations en lien avec les promotions et ruptures.",
        "\n## Recommandations actionnables\n- Réapprovisionner les best-sellers; tester une promotion ciblée.",
        "\n## Risques & points de vigilance\n- Risque de rupture prolongée sur certains SKU.",
    ])
    return "\n".join(md)


if __name__ == '__main__':
    try:
        from waitress import serve
        serve(app, host='0.0.0.0', port=5001)
    except Exception:
        # Fallback to Flask dev server if waitress is not available
        app.run(host='0.0.0.0', port=5001)
