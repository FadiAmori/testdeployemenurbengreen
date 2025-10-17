"""
Generate event_id_mapping.json to map ML event IDs to Laravel database IDs.
This is a simple 1:1 mapping - update this script if your ML IDs differ from DB IDs.
"""
import pandas as pd
import json
import os

# Read the events dataset
df_events = pd.read_csv('datasets/urban_events_details.csv')

# Create mapping: extract numeric part from ML event_id (e.g., 'event_9935' -> '9935')
# and map it to the same value (assuming ML IDs match DB IDs)
# If your DB IDs are different, you need to fetch them from Laravel and update this mapping
mapping = {}
for event_id in df_events['event_id'].unique():
    # Extract numeric part (e.g., 'event_9935' -> '9935')
    ml_numeric = event_id.split('_')[1] if '_' in event_id else event_id
    # For now, use 1:1 mapping (ML ID = DB ID)
    # TODO: If DB IDs differ, fetch real IDs from Laravel API and update this
    mapping[ml_numeric] = ml_numeric

# Save mapping
os.makedirs('models', exist_ok=True)
with open('models/event_id_mapping.json', 'w') as f:
    json.dump(mapping, f, indent=2)

print(f"✓ Generated mapping for {len(mapping)} events")
print(f"✓ Saved to models/event_id_mapping.json")
print("\nNote: This uses 1:1 mapping (ML ID = DB ID)")
print("If your Laravel database uses different IDs, update this mapping manually or via API.")
