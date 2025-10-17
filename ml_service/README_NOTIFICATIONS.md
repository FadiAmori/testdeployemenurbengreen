# 🤖 Notification AI Service

Service d'intelligence artificielle pour générer automatiquement des notifications de maintenance pour les plantes.

## 📋 Fonctionnalités

- **Recommandations intelligentes** : Suggère des notifications appropriées basées sur le nom de la plante
- **Correspondance floue** : Trouve des plantes similaires même si le nom n'est pas exact
- **Format structuré** : Retourne titre, message, fréquence (jours) et heure pour chaque notification
- **API REST** : Intégration facile avec Laravel

## 🚀 Démarrage

### Installation des dépendances

```bash
cd ml_service
pip install -r requirements.txt
```

### Lancement du service

```bash
python notf.py
```

Le service démarre sur **http://localhost:5002**

## 📡 API Endpoints

### 1. Générer des notifications

**GET** `/api/generate-notifications?plant={nom_plante}`

**Exemple:**
```bash
curl "http://localhost:5002/api/generate-notifications?plant=Tomate"
```

**Réponse:**
```json
{
  "plant": "Tomate",
  "notifications": [
    {
      "title": "Notification",
      "message": "Taillez les feuilles fanées cette semaine.",
      "days": 7,
      "time": "13:20"
    },
    {
      "title": "Notification",
      "message": "N'oubliez pas la fertilisation hebdomadaire.",
      "days": 7,
      "time": "13:20"
    }
  ],
  "count": 2
}
```

### 2. Health Check

**GET** `/health`

**Réponse:**
```json
{
  "status": "healthy",
  "model_loaded": true,
  "plants_count": 100
}
```

## 🔗 Intégration Laravel

### 1. Dans votre contrôleur

```php
use Illuminate\Support\Facades\Http;

public function getAINotifications($productName)
{
    try {
        $response = Http::timeout(10)->get('http://localhost:5002/api/generate-notifications', [
            'plant' => $productName
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['notifications'] ?? [];
        }
        
        return [];
    } catch (\Exception $e) {
        \Log::error('AI Notification Service Error: ' . $e->getMessage());
        return [];
    }
}
```

### 2. Créer automatiquement les notifications

```php
public function syncAINotifications(Product $product)
{
    $aiNotifications = $this->getAINotifications($product->name);
    
    foreach ($aiNotifications as $notif) {
        // Vérifier si la notification existe déjà
        $exists = Notification::where('title', $notif['title'])
                             ->where('message', $notif['message'])
                             ->exists();
        
        if (!$exists) {
            // Créer la notification
            $notification = Notification::create([
                'title' => $notif['title'],
                'message' => $notif['message'],
                'type' => 'maintenance',
                'status' => 'active'
            ]);
            
            // Attacher au produit avec la planification
            $product->notifications()->attach($notification->id, [
                'days' => $notif['days'],
                'time' => $notif['time']
            ]);
        }
    }
}
```

### 3. Dans votre vue Blade

```blade
<button class="btn btn-primary" onclick="generateAINotifications({{ $product->id }})">
    🤖 Générer Notifications IA
</button>

<script>
function generateAINotifications(productId) {
    fetch(`/api/products/${productId}/ai-notifications`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.count} notifications générées!`);
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>
```

## 📊 Dataset

Le dataset se trouve dans `public/datasets/plante_notifications.csv`

**Format:**
```csv
nom_plante,message_notification_1,date_notification_1,message_notification_2,date_notification_2
Tomate,Taillez les feuilles fanées cette semaine.,2025-10-17 13:20,N'oubliez pas la fertilisation hebdomadaire.,2025-10-26 13:20
```

## 🛠️ Architecture

```
ml_service/
├── notf.py                          # Service principal
├── models/
│   └── notifications/
│       ├── vectorizer_notif.joblib  # Vectorizer TF-IDF
│       ├── nn_notif.joblib          # Nearest Neighbors model
│       └── data_notif.joblib        # Cached dataset
public/datasets/
└── plante_notifications.csv         # Dataset source
```

## ⚙️ Configuration

- **Port**: 5002 (modifiable dans `notf.py`)
- **CORS**: Activé pour toutes les origines
- **Timeout recommandé**: 10 secondes
- **Nombre max de notifications**: 20 par plante

## 🐛 Dépannage

### Le service ne démarre pas
```bash
# Vérifier les dépendances
pip install flask flask-cors pandas scikit-learn joblib waitress

# Vérifier que le port 5002 est libre
netstat -ano | findstr :5002
```

### Dataset non trouvé
```bash
# Vérifier le chemin
python -c "import os; print(os.path.abspath('../public/datasets/plante_notifications.csv'))"
```

### Modèle ne se charge pas
```bash
# Supprimer le cache et réentraîner
rm -rf ml_service/models/notifications/
python notf.py
```

## 📝 Notes

- Le modèle utilise TF-IDF avec n-grams (2,4) pour la correspondance floue
- Les notifications sont dédupliquées par titre et message
- Le temps par défaut est extrait des données d'origine
- La fréquence par défaut est de 7 jours (hebdomadaire)

## 🚀 Production

Pour déployer en production, utilisez un serveur WSGI comme Gunicorn ou Waitress (déjà inclus).

```bash
# Déjà configuré dans notf.py
waitress-serve --host=0.0.0.0 --port=5002 notf:app
```

---

**Auteur**: UrbanGreen Team
**Date**: Octobre 2025
