# Plant Steps ML Service

This small Flask service trains a simple TF-IDF + nearest-neighbors model on `public/datasets/plant_steps_dataset.csv` and exposes `/api/generate-steps?plant=NAME` to return recommended steps.

Setup

1. Create a Python virtualenv and activate it.

   python -m venv venv
   # Windows PowerShell
   .\\venv\\Scripts\\Activate.ps1

2. Install dependencies

   pip install -r requirements.txt

3. Run the service

   python app.py

The service will train on the CSV and persist artifacts into `ml_service/models/`.

Usage

From Laravel/JS you can call:

GET http://127.0.0.1:5001/api/generate-steps?plant=Tomato

It returns JSON array of {title, description} objects or a 404/message when no steps are found.

Notes

- The model uses character n-gram TF-IDF on plant names to allow fuzzy matches.
- Deduplication is applied on (title, description) pair.
- Increase `n_neighbors` or tweak vectorizer params for different behavior.
