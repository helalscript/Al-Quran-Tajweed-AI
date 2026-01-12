<?php

namespace App\Services\API\V1\User;

use App\Events\MemorizationAIResponse;
use App\Models\MemorizationMistake;
use App\Models\MemorizationSession;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MemorizationService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Create a new memorization session.
     */
    public function createSession(array $data)
    {
        try {
            // Fetch original text from Al-Quran API
            $originalText = $this->fetchOriginalText($data);

            $session = MemorizationSession::create([
                'user_id' => $this->user->id,
                'surah_id' => $data['surah_id'] ?? null,
                'surah_name' => $data['surah_name'] ?? null,
                'ayah_id' => $data['ayah_id'] ?? null,
                'ayah_text' => $data['ayah_text'] ?? null,
                'ayah_start' => $data['ayah_start'] ?? null,
                'ayah_end' => $data['ayah_end'] ?? null,
                'original_text' => $originalText,
                'user_recitation' => '',
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            return $session->load('mistakes');
        } catch (Exception $e) {
            Log::error('MemorizationService::createSession' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch original text from Al-Quran API based on surah and ayah.
     */
    protected function fetchOriginalText(array $data): string
    {
        try {
            $surahId = $data['surah_id'] ?? null;
            $ayahId = $data['ayah_id'] ?? null;

            if (!$surahId || !$ayahId) {
                throw new Exception('Surah ID and Ayah ID are required');
            }

            // Fetch surah with specific ayah
            $apiUrl = "https://api.alquran.cloud/v1/surah/{$surahId}/quran-uthmani";
            $response = Http::get($apiUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch original text from Al-Quran API');
            }

            $surahData = $response->json('data');
            $ayahs = $surahData['ayahs'] ?? [];

            // Find the specific ayah
            foreach ($ayahs as $ayah) {
                if ($ayah['numberInSurah'] == $ayahId) {
                    return $ayah['text'] ?? '';
                }
            }

            throw new Exception('Ayah not found');
        } catch (Exception $e) {
            Log::error('MemorizationService::fetchOriginalText' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process recitation and send to AI for comparison.
     */
    public function processRecitation(string $sessionId, string $userText)
    {
        try {
            $session = MemorizationSession::where('user_id', $this->user->id)
                ->where('id', $sessionId)
                ->first();

            if (!$session) {
                throw new Exception('Session not found');
            }

            // Update user recitation
            $session->update([
                'user_recitation' => $userText,
            ]);

            // Process with AI
            $aiResponse = $this->streamAIResponse($session, $userText);

            // Analyze mistakes and save
            $this->analyzeMistakes($session, $aiResponse);

            // Calculate accuracy
            $accuracy = $this->calculateAccuracy($session);

            // Update session
            $session->update([
                'accuracy_score' => $accuracy,
                'total_mistakes' => $session->mistakes()->count(),
            ]);

            return [
                'session' => $session->fresh(['mistakes']),
                'accuracy' => $accuracy,
            ];
        } catch (Exception $e) {
            Log::error('MemorizationService::processRecitation' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Stream AI response via Reverb (using Laravel AI/Prism package).
     * Response format optimized for Flutter to identify word-level mistakes.
     */
    protected function streamAIResponse(MemorizationSession $session, string $userText): string
    {
        try {
            $originalText = $session->original_text;

            // Prepare AI prompt for comparison
            $prompt = "Compare these two Arabic texts word by word and identify all mistakes.\n\n";
            $prompt .= "ORIGINAL TEXT (from Quran):\n{$originalText}\n\n";
            $prompt .= "USER RECITED TEXT (voice converted):\n{$userText}\n\n";
            $prompt .= "Task: Analyze and identify mistakes.\n\n";
            $prompt .= "IMPORTANT: Return response as JSON only with this exact structure:\n";
            $prompt .= "{\n";
            $prompt .= "  \"mistakes\": [\n";
            $prompt .= "    {\n";
            $prompt .= "      \"type\": \"missing_word|wrong_word|extra_word|pronunciation\",\n";
            $prompt .= "      \"word_position\": 0, // Index in original text (0-based)\n";
            $prompt .= "      \"sentence_position\": 0, // Index in sentence (for Flutter UI)\n";
            $prompt .= "      \"original_word\": \"بِسْمِ\",\n";
            $prompt .= "      \"user_word\": \"بسم\", // What user said (if wrong) or null if missing\n";
            $prompt .= "      \"confidence\": 0.95, // AI confidence (0-1)\n";
            $prompt .= "      \"suggestion\": \"Add missing word\"\n";
            $prompt .= "    }\n";
            $prompt .= "  ],\n";
            $prompt .= "  \"accuracy\": 85.5, // Overall accuracy percentage\n";
            $prompt .= "  \"analysis\": \"Brief analysis text\"\n";
            $prompt .= "}\n\n";
            $prompt .= "Return ONLY valid JSON, no other text.";

            // TODO: Replace with your Laravel AI package (Prism/OpenAI/etc)
            // For now, using a placeholder - you'll need to integrate your AI package
            // Example with OpenAI (if you have openai-php/laravel):
            /*
            $stream = \OpenAI::chat()->createStreamed([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert in Arabic Quranic text comparison.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
            ]);

            $fullResponse = '';
            $mistakes = [];
            foreach ($stream as $response) {
                $chunk = $response->choices[0]->delta->content ?? '';
                $fullResponse .= $chunk;

                // Try to parse JSON if complete
                $jsonData = json_decode($fullResponse, true);
                if ($jsonData && isset($jsonData['mistakes'])) {
                    $mistakes = $jsonData['mistakes'];
                }

                // Broadcast chunk to Reverb channel in real-time
                broadcast(new MemorizationAIResponse(
                    sessionId: $session->id,
                    chunk: $chunk,
                    userId: $this->user->id,
                    mistakes: $mistakes,
                    isComplete: false
                ))->toOthers();
            }

            // Final broadcast with complete data
            $finalData = json_decode($fullResponse, true);
            if ($finalData) {
                broadcast(new MemorizationAIResponse(
                    sessionId: $session->id,
                    chunk: '',
                    userId: $this->user->id,
                    mistakes: $finalData['mistakes'] ?? [],
                    isComplete: true
                ))->toOthers();
            }
            */

            // Placeholder response structure for now
            // Replace this with actual AI streaming
            // This is the format Flutter will receive:
            $fullResponse = json_encode([
                'mistakes' => [],
                'accuracy' => 100,
                'analysis' => 'AI response will be integrated here. Replace with your AI package integration.',
            ], JSON_UNESCAPED_UNICODE);

            // Broadcast placeholder (remove when AI is integrated)
            broadcast(new MemorizationAIResponse(
                sessionId: $session->id,
                chunk: 'Waiting for AI integration...',
                userId: $this->user->id,
                mistakes: [],
                isComplete: true
            ))->toOthers();

            // Save AI response
            $session->update([
                'ai_response' => $fullResponse,
            ]);

            return $fullResponse;
        } catch (Exception $e) {
            Log::error('MemorizationService::streamAIResponse' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Analyze AI response and save mistakes to database.
     */
    protected function analyzeMistakes(MemorizationSession $session, string $aiResponse)
    {
        try {
            $responseData = json_decode($aiResponse, true);

            if (!isset($responseData['mistakes']) || !is_array($responseData['mistakes'])) {
                return;
            }

            // Delete existing mistakes
            $session->mistakes()->delete();

            // Save new mistakes
            foreach ($responseData['mistakes'] as $mistake) {
                MemorizationMistake::create([
                    'memorization_session_id' => $session->id,
                    'mistake_type' => $mistake['type'] ?? 'wrong_word',
                    'word_position' => $mistake['word_position'] ?? null,
                    'sentence_position' => $mistake['sentence_position'] ?? null,
                    'original_word' => $mistake['original_word'] ?? '',
                    'user_word' => $mistake['user_word'] ?? null,
                    'confidence_score' => isset($mistake['confidence']) ? ($mistake['confidence'] * 100) : null,
                    'suggestion' => $mistake['suggestion'] ?? null,
                ]);
            }
        } catch (Exception $e) {
            Log::error('MemorizationService::analyzeMistakes' . $e->getMessage());
            // Don't throw - continue even if mistake parsing fails
        }
    }

    /**
     * Calculate accuracy score.
     */
    protected function calculateAccuracy(MemorizationSession $session): float
    {
        try {
            $totalWords = count(explode(' ', $session->original_text));
            $mistakesCount = $session->mistakes()->count();

            if ($totalWords == 0) {
                return 0;
            }

            $accuracy = (($totalWords - $mistakesCount) / $totalWords) * 100;

            return round($accuracy, 2);
        } catch (Exception $e) {
            Log::error('MemorizationService::calculateAccuracy' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get session by ID.
     */
    public function getSession(string $id)
    {
        try {
            return MemorizationSession::where('user_id', $this->user->id)
                ->with('mistakes')
                ->find($id);
        } catch (Exception $e) {
            Log::error('MemorizationService::getSession' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * End a memorization session.
     */
    public function endSession(string $id)
    {
        try {
            $session = MemorizationSession::where('user_id', $this->user->id)
                ->find($id);

            if (!$session) {
                throw new Exception('Session not found');
            }

            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return $session->fresh(['mistakes']);
        } catch (Exception $e) {
            Log::error('MemorizationService::endSession' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user's memorization history.
     */
    public function getHistory($request)
    {
        try {
            $perPage = $request->per_page ?? 25;

            return MemorizationSession::where('user_id', $this->user->id)
                ->with('mistakes')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('MemorizationService::getHistory' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get mistakes for a session.
     */
    public function getMistakes(string $id)
    {
        try {
            $session = MemorizationSession::where('user_id', $this->user->id)
                ->find($id);

            if (!$session) {
                throw new Exception('Session not found');
            }

            return $session->mistakes()->orderBy('word_position')->get();
        } catch (Exception $e) {
            Log::error('MemorizationService::getMistakes' . $e->getMessage());
            throw $e;
        }
    }
}

