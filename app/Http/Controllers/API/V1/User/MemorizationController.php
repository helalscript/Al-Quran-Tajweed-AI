<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\MemorizationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemorizationController extends Controller
{
    public function __construct(protected MemorizationService $memorizationService)
    {
        //
    }

    /**
     * Start a new memorization session.
     */
    public function startSession(Request $request)
    {
        $validatedData = $request->validate([
            'surah_id' => 'required_without:surah_name|integer',
            'surah_name' => 'required_without:surah_id|string|max:255',
            'ayah_id' => 'required_without:ayah_text|integer',
            'ayah_text' => 'required_without:ayah_id|string',
            'ayah_start' => 'sometimes|integer',
            'ayah_end' => 'sometimes|integer',
        ]);

        try {
            $session = $this->memorizationService->createSession($validatedData);

            return Helper::jsonResponse(
                true,
                'Memorization session started successfully',
                200,
                [
                    'session' => $session,
                    'channel_name' => 'memorization.user.' . auth()->id(),
                ]
            );
        } catch (Exception $e) {
            Log::error('MemorizationController::startSession' . $e->getMessage());

            return Helper::jsonErrorResponse('Failed to start memorization session', 500);
        }
    }

    /**
     * Get session details.
     */
    public function getSession(string $id)
    {
        try {
            $session = $this->memorizationService->getSession($id);

            if (!$session) {
                return Helper::jsonErrorResponse('Session not found', 404);
            }

            return Helper::jsonResponse(
                true,
                'Session retrieved successfully',
                200,
                $session
            );
        } catch (Exception $e) {
            Log::error('MemorizationController::getSession' . $e->getMessage());

            return Helper::jsonErrorResponse('Failed to retrieve session', 500);
        }
    }

    /**
     * Stream recitation to AI and receive real-time feedback.
     */
    public function streamRecitation(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'user_text' => 'required|string',
        ]);

        try {
            $result = $this->memorizationService->processRecitation($id, $validatedData['user_text']);

            return Helper::jsonResponse(
                true,
                'Recitation processed successfully',
                200,
                $result
            );
        } catch (Exception $e) {
            Log::error('MemorizationController::streamRecitation' . $e->getMessage());

            return Helper::jsonErrorResponse('Failed to process recitation', 500);
        }
    }

    /**
     * End a memorization session.
     */
    public function endSession(string $id)
    {
        try {
            $session = $this->memorizationService->endSession($id);

            return Helper::jsonResponse(
                true,
                'Session ended successfully',
                200,
                $session
            );
        } catch (Exception $e) {
            Log::error('MemorizationController::endSession' . $e->getMessage());

            return Helper::jsonErrorResponse('Failed to end session', 500);
        }
    }

    /**
     * Get user's memorization history.
     */
    public function getHistory(Request $request)
    {
        try {
            $history = $this->memorizationService->getHistory($request);

            return Helper::jsonResponse(
                true,
                'History retrieved successfully',
                200,
                $history,
                true
            );
        } catch (Exception $e) {
            Log::error('MemorizationController::getHistory' . $e->getMessage());

            return Helper::jsonErrorResponse('Failed to retrieve history', 500);
        }
    }

    /**
     * Get mistakes for a session.
     */
    public function getMistakes(string $id)
    {
        try {
            $mistakes = $this->memorizationService->getMistakes($id);

            return Helper::jsonResponse(
                true,
                'Mistakes retrieved successfully',
                200,
                $mistakes
            );
        } catch (Exception $e) {
            Log::error('MemorizationController::getMistakes' . $e->getMessage());

            return Helper::jsonErrorResponse('Failed to retrieve mistakes', 500);
        }
    }
}

