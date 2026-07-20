<?php

namespace App\Http\Controllers\API\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuranReadingHistory;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class QuranReadingHistoryController extends Controller
{
    public function saveLastRead(Request $request)
    {
        try {
            $request->validate([
                'surah_number' => 'nullable|integer',
                'ayah_number' => 'nullable|integer',
                'page_number' => 'nullable|integer',
            ]);

            $history = QuranReadingHistory::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'surah_number' => $request->surah_number,
                    'ayah_number' => $request->ayah_number,
                    'page_number' => $request->page_number,
                    'last_read_at' => now(),
                ]
            );

            return Helper::jsonResponse(true, 'Reading history saved successfully', 200, $history);
        } catch (Exception $e) {
            Log::error('QuranReadingHistoryController::saveLastRead ' . $e->getMessage());
            return Helper::jsonErrorResponse('Failed to save reading history', 500);
        }
    }

    public function getLastRead()
    {
        try {
            $history = QuranReadingHistory::where('user_id', Auth::id())->first();

            return Helper::jsonResponse(true, 'Last read fetched successfully', 200, $history);
        } catch (Exception $e) {
            Log::error('QuranReadingHistoryController::getLastRead ' . $e->getMessage());
            return Helper::jsonErrorResponse('Failed to fetch reading history', 500);
        }
    }
}
