# Profanity Filter Setup Guide

## Overview
This profanity filter prevents users from posting statutes or comments containing inappropriate language.

## Files Created
1. `ml_service/profanity_filter.py` - Flask API for profanity detection
2. `app/Http/Middleware/CheckProfanity.php` - Laravel middleware
3. Updated `app/Http/Kernel.php` - Registered middleware
4. Updated `routes/web.php` - Applied middleware to routes

## Setup Instructions

### 1. Prepare the Profanity Dataset

Place your `profanity_en.csv` file in:
```
public/datasets/profanity_en.csv
```

**Dataset Format:**
The CSV should have at least one column containing bad words. Common column names:
- `word`
- `profanity`
- `text`
- `bad_word`
- `term`

Example CSV:
```csv
word
badword1
badword2
badword3
```

### 2. Install Python Dependencies

```powershell
cd ml_service
pip install flask flask-cors pandas scikit-learn joblib waitress
```

### 3. Start the Profanity Filter API

```powershell
cd ml_service
python profanity_filter.py
```

The API will run on `http://127.0.0.1:5002`

**To run in background (production):**
```powershell
# Windows
start /B python profanity_filter.py

# Or use a process manager like PM2:
pm2 start profanity_filter.py --name profanity-filter --interpreter python
```

### 4. Test the API

**Health Check:**
```powershell
curl http://127.0.0.1:5002/api/health
```

**Test Profanity Detection:**
```powershell
curl -X POST http://127.0.0.1:5002/api/check-profanity `
  -H "Content-Type: application/json" `
  -d '{"text":"This is a test message","type":"comment"}'
```

## API Endpoints

### POST /api/check-profanity
Check any text for profanity.

**Request:**
```json
{
  "text": "Your text here",
  "type": "statute" or "comment" (optional)
}
```

**Response:**
```json
{
  "is_clean": true/false,
  "found_words": ["word1", "word2"],
  "censored_text": "Text with *** replacing bad words",
  "severity": "none|medium|high"
}
```

### POST /api/check-statute
Check statute title and description.

**Request:**
```json
{
  "titre": "Statute title",
  "description": "Statute description"
}
```

### POST /api/check-comment
Check comment text.

**Request:**
```json
{
  "description": "Comment text"
}
```

### GET /api/health
Health check endpoint.

## How It Works

### 1. User submits a statute or comment
### 2. Laravel middleware intercepts the request
### 3. Middleware calls Flask API to check for profanity
### 4. If profanity detected:
   - API request: Returns 422 error with found words
   - Web request: Redirects back with error message
### 5. If clean: Request proceeds normally

## Configuration

### Change API URL
Edit `app/Http/Middleware/CheckProfanity.php`:
```php
private $apiUrl = 'http://your-server:5002/api/check-profanity';
```

### Change API Port
Edit `ml_service/profanity_filter.py`:
```python
serve(app, host='0.0.0.0', port=5002)  # Change port here
```

### Disable Profanity Check
Remove middleware from routes in `routes/web.php`:
```php
// Remove ->middleware('profanity.check')
Route::post('statutes/{statute}/comentes', [ComenteController::class, 'store'])->name('comentes.store');
```

## Protected Routes

The following routes are protected:
- ✅ Creating statutes (POST /statutes)
- ✅ Updating statutes (PUT /statutes/{id})
- ✅ Creating comments (POST /statutes/{id}/comentes)
- ✅ Updating comments (PUT /comentes/{id})
- ✅ Admin statute creation/update
- ✅ Admin comment creation/update

## Troubleshooting

### Error: "Profanity filter not available"
- Check if Flask API is running
- Verify API URL in middleware
- Check firewall/network settings

### Error: "Connection timeout"
- Increase timeout in middleware:
  ```php
  $response = Http::timeout(10)->post(...);  // 10 seconds
  ```

### API not detecting words
- Verify CSV file exists and has correct format
- Check logs in terminal running Flask app
- Test API directly with curl

### False positives
- Edit your CSV to remove words that shouldn't be filtered
- Delete `ml_service/models/profanity_words.joblib` to force reload
- Restart Flask API

## Logs

### Laravel Logs
Check `storage/logs/laravel.log` for:
- Profanity detection warnings
- API connection errors

### Flask Logs
Check terminal running Flask app for:
- Loaded word count
- API requests
- Errors

## Production Deployment

### Use a Process Manager
```bash
# PM2 (recommended)
pm2 start ml_service/profanity_filter.py --name profanity-api --interpreter python
pm2 save
pm2 startup

# Or use systemd, supervisor, etc.
```

### Add to Laravel Queue
For better performance, consider making profanity checks asynchronous using Laravel queues.

### Cache Results
Add caching layer to reduce API calls for repeated content.

## Testing

```powershell
# Test statute creation with profanity
curl -X POST http://127.0.0.1:8000/statutes `
  -H "Content-Type: application/json" `
  -H "X-CSRF-TOKEN: your-token" `
  -d '{"titre":"Test","description":"Contains badword"}'

# Should return 422 error with profanity details
```

## Support

If you encounter issues:
1. Check logs (Laravel + Flask)
2. Verify CSV format
3. Test API independently
4. Check middleware is registered
5. Verify routes have middleware applied
