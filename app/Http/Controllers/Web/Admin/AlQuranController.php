<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Surah;
use App\Models\AyahEdition;
use Exception;
use Illuminate\Support\Facades\Log;

class AlQuranController extends Controller
{
    public function index()
    {
        $surahs = Surah::orderBy('number')->get();
        $history = \App\Models\QuranReadingHistory::where('user_id', auth()->id())->first();
        
        $historyData = null;
        if ($history) {
            $surah = null;
            if ($history->surah_number) {
                $surah = Surah::where('number', $history->surah_number)->first();
            }
            $historyData = [
                'surah_number' => $history->surah_number,
                'ayah_number' => $history->ayah_number,
                'page_number' => $history->page_number,
                'surah_name' => $surah ? $surah->english_name : null,
                'surah_arabic' => $surah ? $surah->name : null,
                'last_read_at' => $history->last_read_at,
            ];
        }

        return Inertia::render('admin/al-quran/Index', [
            'surahs' => $surahs,
            'history' => $historyData,
        ]);
    }

    public function show(Request $request, $surah)
    {
        if ($request->has('translation')) {
            session(['quran_admin_translation' => $request->query('translation')]);
        }
        if ($request->has('audio')) {
            session(['quran_admin_audio' => $request->query('audio')]);
        }
        if ($request->has('font')) {
            session(['quran_admin_font' => $request->query('font')]);
        }
        if ($request->has('font_size')) {
            session(['quran_admin_font_size' => $request->query('font_size')]);
        }

        $translationEdition = session('quran_admin_translation');
        $audioEdition = session('quran_admin_audio');
        $fontFamily = session('quran_admin_font', 'Uthmanic, Arial, serif');
        $fontSize = session('quran_admin_font_size', '30');

        $alQuranService = app(\App\Services\API\V1\User\AlQuranService::class);
        $surahData = $alQuranService->getSurahByNumber($surah, 'quran-uthmani', $translationEdition, $audioEdition);

        $translations = \App\Models\Edition::where('format', 'text')
            ->where('type', 'translation')
            ->get();
        
        $audios = \App\Models\Edition::where('format', 'audio')
            ->get();

        $surahs = \App\Models\Surah::orderBy('number')->get();

        return Inertia::render('admin/al-quran/Show', [
            'surahId' => $surah,
            'surahData' => $surahData['data'] ?? null,
            'translations' => $translations,
            'audios' => $audios,
            'currentTranslation' => $translationEdition,
            'currentAudio' => $audioEdition,
            'currentFont' => $fontFamily,
            'currentFontSize' => $fontSize,
            'surahs' => $surahs,
        ]);
    }

    public function showPage(Request $request, $page)
    {
        if ($request->has('translation')) {
            session(['quran_admin_translation' => $request->query('translation')]);
        }
        if ($request->has('audio')) {
            session(['quran_admin_audio' => $request->query('audio')]);
        }
        if ($request->has('font')) {
            session(['quran_admin_font' => $request->query('font')]);
        }
        if ($request->has('font_size')) {
            session(['quran_admin_font_size' => $request->query('font_size')]);
        }

        $translationEdition = session('quran_admin_translation');
        $audioEdition = session('quran_admin_audio');
        $fontFamily = session('quran_admin_font', 'Uthmanic, Arial, serif');
        $fontSize = session('quran_admin_font_size', '30');

        $alQuranService = app(\App\Services\API\V1\User\AlQuranService::class);
        
        // Ensure default editions are set if missing in session
        if (!$translationEdition || !$audioEdition) {
            // we let the service fallback to DB defaults inside getPage
            // but wait, getPage handles user defaults inside it if null
        }

        $data = $alQuranService->getPage($page, 'quran-uthmani');

        $translations = \App\Models\Edition::where('format', 'text')
            ->where('type', 'translation')
            ->get();
        
        $audios = \App\Models\Edition::where('format', 'audio')
            ->get();

        return Inertia::render('admin/al-quran/ShowPage', [
            'pageNum' => $page,
            'pageData' => $data['data'] ?? null,
            'translations' => $translations,
            'audios' => $audios,
            'currentTranslation' => $translationEdition,
            'currentAudio' => $audioEdition,
            'currentFont' => $fontFamily,
            'currentFontSize' => $fontSize,
        ]);
    }

    public function updateAyah(Request $request, $ayah_id, $edition_id)
    {
        try {
            $request->validate([
                'text' => 'required|string',
            ]);

            $ayahEdition = AyahEdition::where('ayah_id', $ayah_id)
                ->where('edition_id', $edition_id)
                ->firstOrFail();

            $ayahEdition->update([
                'text' => $request->text,
            ]);

            return back()->with('success', 'Ayah updated successfully.');
        } catch (Exception $e) {
            Log::error('Web\Admin\AlQuranController::updateAyah ' . $e->getMessage());
            return back()->with('error', 'Failed to update Ayah.');
        }
    }
}
