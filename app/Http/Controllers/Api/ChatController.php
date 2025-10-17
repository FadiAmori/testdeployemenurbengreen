<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Chat with AI for plant maintenance questions
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        // Validate request
        $request->validate([
            'question' => 'required|string|min:3|max:500'
        ]);

        $question = $request->input('question');

        try {
            // Call Python ML service
            $chatServiceUrl = env('ML_CHAT_SERVICE_URL', 'http://localhost:5003');
            
            $response = Http::timeout(30)->post($chatServiceUrl . '/api/chat', [
                'question' => $question
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'question' => $question,
                        'answer' => $data['answer'] ?? 'Aucune réponse disponible.',
                        'confidence' => $data['confidence'] ?? 0,
                        'plant' => $data['plant'] ?? null,
                        'sources' => $data['sources'] ?? []
                    ]
                ]);
            } else {
                Log::error('Chat service error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Le service de chat est temporairement indisponible.'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite. Veuillez réessayer.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get chat service health status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        try {
            $chatServiceUrl = env('ML_CHAT_SERVICE_URL', 'http://localhost:5003');
            
            $response = Http::timeout(5)->get($chatServiceUrl . '/health');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'service' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Service unavailable'
                ], 503);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service unreachable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suggested questions for users
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions()
    {
        try {
            $chatServiceUrl = env('ML_CHAT_SERVICE_URL', 'http://localhost:5003');
            
            $response = Http::timeout(10)->get($chatServiceUrl . '/api/chat/suggestions');

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Fallback to local suggestions if service is unavailable
        }

        // Fallback suggestions
        $suggestions = [
            [
                'category' => 'Arrosage',
                'icon' => '💧',
                'questions' => [
                    'Comment arroser ma tomate ?',
                    'À quelle fréquence arroser mes plantes ?',
                    'Comment savoir si ma plante a besoin d\'eau ?'
                ]
            ],
            [
                'category' => 'Lumière',
                'icon' => '☀️',
                'questions' => [
                    'Quelle exposition pour ma plante ?',
                    'Ma plante peut-elle supporter le soleil direct ?',
                    'Comment améliorer la lumière pour mes plantes ?'
                ]
            ],
            [
                'category' => 'Fertilisation',
                'icon' => '🌱',
                'questions' => [
                    'Quand fertiliser mes tomates ?',
                    'Quel engrais utiliser pour mes plantes ?',
                    'Comment nourrir mes plantes en hiver ?'
                ]
            ],
            [
                'category' => 'Taille',
                'icon' => '✂️',
                'questions' => [
                    'Comment tailler ma plante ?',
                    'Quand couper les feuilles mortes ?',
                    'Comment favoriser la croissance par la taille ?'
                ]
            ],
            [
                'category' => 'Entretien général',
                'icon' => '🌿',
                'questions' => [
                    'Comment prendre soin de mes plantes ?',
                    'Quelle température pour mes plantes d\'intérieur ?',
                    'Comment augmenter l\'humidité pour mes plantes ?'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }
}
