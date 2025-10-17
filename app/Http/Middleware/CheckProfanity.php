<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckProfanity
{
    /**
     * Profanity filter API endpoint
     */
    private $apiUrl = 'http://127.0.0.1:50099/api/check-profanity';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Fields to check for profanity
        $fieldsToCheck = ['titre', 'description'];
        
        $textsToCheck = [];
        foreach ($fieldsToCheck as $field) {
            if ($request->has($field) && !empty($request->input($field))) {
                $textsToCheck[] = $request->input($field);
            }
        }

        if (empty($textsToCheck)) {
            return $next($request);
        }

        // Combine all text for checking
        $combinedText = implode(' ', $textsToCheck);

        try {
            // Call profanity filter API
            $response = Http::timeout(5)->post($this->apiUrl, [
                'text' => $combinedText,
                'type' => $this->getContentType($request)
            ]);

            if ($response->successful()) {
                $result = $response->json();

                // If profanity detected, reject the request
                if (isset($result['is_clean']) && !$result['is_clean']) {
                    $foundWords = $result['found_words'] ?? [];
                    
                    Log::warning('Profanity detected', [
                        'user_id' => auth()->id(),
                        'route' => $request->path(),
                        'found_words' => $foundWords
                    ]);

                    $errorMessage = 'Your content contains inappropriate language. Please remove offensive words and try again.';
                    
                    if (!empty($foundWords)) {
                        $errorMessage .= ' Found: ' . implode(', ', array_unique($foundWords));
                    }

                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => $errorMessage,
                            'found_words' => $foundWords,
                            'severity' => $result['severity'] ?? 'medium'
                        ], 422);
                    }

                    return redirect()->back()
                        ->withErrors(['profanity' => $errorMessage])
                        ->withInput();
                }
            } else {
                // If API is not available, log and allow request to proceed
                Log::warning('Profanity filter API unavailable', [
                    'status' => $response->status(),
                    'route' => $request->path()
                ]);
            }
        } catch (\Exception $e) {
            // If there's an error, log it but allow request to proceed
            Log::error('Profanity filter error: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Determine content type based on request path
     */
    private function getContentType(Request $request): string
    {
        $path = $request->path();
        
        if (str_contains($path, 'statute')) {
            return 'statute';
        } elseif (str_contains($path, 'comente') || str_contains($path, 'comment')) {
            return 'comment';
        }
        
        return 'text';
    }
}
