import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Pause, Play, SkipBack, SkipForward, AlertTriangle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';

interface PageAyah {
    number: number;
    numberInSurah: number;
    arabic: string;
    translation: string;
    audio: string;
    surah: {
        number: number;
        name: string;
        englishName: string;
        englishNameTranslation: string;
    };
}

interface PageData {
    number: number;
    ayahs: PageAyah[];
}

interface Edition {
    id: number;
    identifier: string;
    language: string;
    name: string;
    englishName: string;
    format: string;
    type: string;
}

interface Props {
    pageNum: string;
    pageData: PageData;
    translations?: Edition[];
    audios?: Edition[];
    currentTranslation?: string;
    currentAudio?: string;
    currentFont?: string;
    currentFontSize?: string;
}

export default function ShowPage({ 
    pageNum, 
    pageData,
    translations,
    audios,
    currentTranslation,
    currentAudio,
    currentFont,
    currentFontSize
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Al Quran', href: '/admin/al-quran' },
        { title: `Page ${pageNum}`, href: `/admin/al-quran/page/${pageNum}` },
    ];

    const pagesList = Array.from({ length: 604 }, (_, i) => i + 1);

    const audioRef = useRef<HTMLAudioElement | null>(null);
    const ayahRefs = useRef<(HTMLDivElement | null)[]>([]);

    const [isPlaying, setIsPlaying] = useState(false);
    const [currentIndex, setCurrentIndex] = useState(-1);
    
    // Edit state
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [editingAyah, setEditingAyah] = useState<{ id: number, text: string, type: string } | null>(null);
    const [editText, setEditText] = useState('');

    const ayahsCount = pageData?.ayahs?.length || 0;

    useEffect(() => {
        if (currentIndex >= 0 && currentIndex < ayahsCount) {
            // Scroll to the active ayah
            ayahRefs.current[currentIndex]?.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });

            // Set audio source and play
            if (audioRef.current && isPlaying) {
                const audioUrl = pageData.ayahs[currentIndex]?.audio;
                if (audioUrl) {
                    audioRef.current.src = audioUrl;
                    audioRef.current.play().catch(e => console.error("Playback failed", e));
                }
            }
        }
    }, [currentIndex, isPlaying, pageData]);

    useEffect(() => {
        // Scroll the active page into view in the sidebar upon load
        const activePageBtn = document.getElementById('active-page-btn');
        if (activePageBtn) {
            activePageBtn.scrollIntoView({ behavior: 'auto', block: 'center' });
        }
    }, [pageNum]);

    const playNext = () => {
        if (currentIndex < ayahsCount - 1) {
            setCurrentIndex(prev => prev + 1);
        } else {
            setIsPlaying(false);
            setCurrentIndex(-1);
        }
    };

    const playPrevious = () => {
        if (currentIndex > 0) {
            setCurrentIndex(prev => prev - 1);
        }
    };

    const togglePlayPause = () => {
        if (isPlaying) {
            audioRef.current?.pause();
            setIsPlaying(false);
        } else {
            if (currentIndex === -1) setCurrentIndex(0);
            setIsPlaying(true);
            if (audioRef.current && currentIndex !== -1) {
                audioRef.current.play();
            }
        }
    };

    const handleAyahClick = (index: number) => {
        setCurrentIndex(index);
        setIsPlaying(true);
    };

    const openEditModal = (ayah: PageAyah, type: string) => {
        setEditingAyah({ id: ayah.number, text: type === 'translation' ? ayah.translation : ayah.arabic, type });
        setEditText(type === 'translation' ? ayah.translation : ayah.arabic);
        setEditModalOpen(true);
    };

    const saveEdit = () => {
        if (!editingAyah) return;
        alert('Edit triggered. (To fully save, the backend needs the specific edition_id mapped in the response).');
        setEditModalOpen(false);
    };

    const handleEditionChange = (type: 'translation' | 'audio' | 'font' | 'font_size', identifier: string) => {
        router.get(`/admin/al-quran/page/${pageNum}`, {
            translation: type === 'translation' ? identifier : currentTranslation,
            audio: type === 'audio' ? identifier : currentAudio,
            font: type === 'font' ? identifier : currentFont,
            font_size: type === 'font_size' ? identifier : currentFontSize,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Settings Modal State
    const [settingsOpen, setSettingsOpen] = useState(false);

    if (!pageData || !pageData.ayahs) {
        return <AppLayout breadcrumbs={breadcrumbs}><div className="p-8 text-center">Loading or Data not found...</div></AppLayout>;
    }

    let currentRenderedSurah = -1;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Quran Page ${pageNum}`} />

            <div className="mx-auto flex max-w-7xl flex-col gap-6 p-4 pb-32 lg:flex-row lg:h-[calc(100vh-6rem)]">
                
                {/* Main Content Area (Scrollable independently) */}
                <div className="flex-1 space-y-6 overflow-y-auto pr-2 pb-24 lg:pb-12" id="main-quran-scroll">
                    <div className="mb-8 text-center">
                        <h1 className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                            Page {pageNum}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            Mushuaf View
                        </p>
                    </div>

                    <div className="space-y-6">
                        {pageData.ayahs.map((ayah, index) => {
                            const isActive = currentIndex === index;
                            
                            // Check if we need to render a Surah Header
                            const isNewSurah = ayah.surah.number !== currentRenderedSurah;
                            if (isNewSurah) {
                                currentRenderedSurah = ayah.surah.number;
                            }

                            return (
                                <div key={ayah.number}>
                                    {isNewSurah && (
                                        <div className="my-8 rounded-lg bg-emerald-600 p-6 text-center text-white shadow-md dark:bg-emerald-800">
                                            <h2 className="text-4xl" style={{ fontFamily: 'Uthmanic, serif' }}>
                                                {ayah.surah.name}
                                            </h2>
                                            <p className="mt-2 text-emerald-100">
                                                {ayah.surah.englishName} • {ayah.surah.englishNameTranslation}
                                            </p>
                                        </div>
                                    )}

                                    <Card
                                        ref={el => ayahRefs.current[index] = el}
                                        className={`p-6 transition-all duration-300 ${
                                            isActive 
                                                ? 'ring-2 ring-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/20' 
                                                : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
                                        }`}
                                        onClick={() => handleAyahClick(index)}
                                    >
                                        <div className="flex flex-col gap-6">
                                            <div className="flex items-start justify-between gap-4">
                                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                    {ayah.numberInSurah}
                                                </div>
                                                <div className="flex-1 text-right">
                                                    <p 
                                                        className="leading-loose text-gray-900 dark:text-gray-100" 
                                                        style={{ 
                                                            fontFamily: currentFont || 'Uthmanic, Arial, serif', 
                                                            fontSize: currentFontSize ? `${currentFontSize}px` : '30px',
                                                            lineHeight: '2.5' 
                                                        }}
                                                        dir="rtl"
                                                    >
                                                        {ayah.arabic}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            {ayah.translation && (
                                                <div className="border-t pt-4 text-gray-700 dark:text-gray-300">
                                                    <p className="text-lg leading-relaxed">{ayah.translation}</p>
                                                </div>
                                            )}

                                            {/* Admin Actions */}
                                            <div className="flex justify-end gap-2 border-t pt-4">
                                                <Button variant="ghost" size="sm" onClick={(e) => { e.stopPropagation(); openEditModal(ayah, 'translation'); }}>
                                                    Edit Translation
                                                </Button>
                                            </div>
                                        </div>
                                    </Card>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Right Sidebar (Fixed height) */}
                <div className="w-full shrink-0 space-y-4 lg:w-80 lg:h-full lg:pb-12">
                    <Card className="flex h-full max-h-[calc(100vh-10rem)] flex-col p-4 shadow-sm">
                        <div className="mb-4 flex items-center justify-between border-b pb-2">
                            <h3 className="text-lg font-semibold text-gray-800 dark:text-gray-200">Page List</h3>
                            <Button variant="ghost" size="icon" onClick={() => setSettingsOpen(true)}>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-settings"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                            </Button>
                        </div>
                        <div className="flex-1 overflow-y-auto pr-2" id="sidebar-surah-scroll">
                            <div className="grid grid-cols-4 gap-2">
                                {pagesList.map((p) => {
                                    const isActivePage = String(p) === String(pageNum);
                                    return (
                                        <Button 
                                            key={p}
                                            id={isActivePage ? "active-page-btn" : undefined}
                                            variant={isActivePage ? "default" : "outline"} 
                                            className="w-full justify-center font-normal"
                                            onClick={() => {
                                                router.get(`/admin/al-quran/page/${p}`, {
                                                    translation: currentTranslation,
                                                    audio: currentAudio,
                                                });
                                            }}
                                        >
                                            {p}
                                        </Button>
                                    );
                                })}
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            {/* Settings Modal */}
            <Dialog open={settingsOpen} onOpenChange={setSettingsOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Quran Display Settings</DialogTitle>
                        <DialogDescription>
                            Select your preferred translation and audio reciter for the Quran reading view.
                        </DialogDescription>
                    </DialogHeader>
                    
                    <div className="my-4 space-y-4">
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Quran Font
                            </label>
                            <select 
                                className="w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                value={currentFont || 'Uthmanic, Arial, serif'}
                                onChange={(e) => handleEditionChange('font' as any, e.target.value)}
                            >
                                <option value="Uthmanic, Arial, serif">Uthmanic (Default)</option>
                                <option value="'Amiri Quran', serif">Amiri Quran</option>
                                <option value="'Noto Naskh Arabic', serif">Noto Naskh Arabic</option>
                                <option value="'Lateef', serif">Lateef</option>
                                <option value="'Reem Kufi', sans-serif">Reem Kufi</option>
                                <option value="'Tajawal', sans-serif">Tajawal</option>
                                <option value="'Cairo', sans-serif">Cairo</option>
                                <option value="'Scheherazade New', serif">Scheherazade New</option>
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Font Size
                            </label>
                            <select 
                                className="w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                value={currentFontSize || '30'}
                                onChange={(e) => handleEditionChange('font_size' as any, e.target.value)}
                            >
                                <option value="20">Small (20px)</option>
                                <option value="24">Medium-Small (24px)</option>
                                <option value="30">Normal (30px)</option>
                                <option value="36">Large (36px)</option>
                                <option value="42">Extra Large (42px)</option>
                                <option value="48">Huge (48px)</option>
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Translation Edition
                            </label>
                            <select 
                                className="w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                value={currentTranslation || ''}
                                onChange={(e) => handleEditionChange('translation', e.target.value)}
                            >
                                <option value="">Default (User Setting)</option>
                                {translations?.map(t => (
                                    <option key={t.identifier} value={t.identifier}>{t.name} ({t.language})</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Audio Reciter
                            </label>
                            <select 
                                className="w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                value={currentAudio || ''}
                                onChange={(e) => handleEditionChange('audio', e.target.value)}
                            >
                                <option value="">Default (User Setting)</option>
                                {audios?.map(a => (
                                    <option key={a.identifier} value={a.identifier}>{a.name}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            {/* Audio Element (Hidden) */}
            <audio 
                ref={audioRef} 
                onEnded={playNext} 
                onError={() => { console.error("Audio error"); playNext(); }} 
            />

            {/* Sticky Player Bottom Bar */}
            <div className="fixed bottom-0 left-0 right-0 z-50 border-t bg-white p-4 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] dark:border-gray-800 dark:bg-gray-900 lg:pl-64">
                <div className="mx-auto flex max-w-4xl items-center justify-between">
                    <div className="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {currentIndex !== -1 ? `Playing Ayah ${pageData.ayahs[currentIndex]?.numberInSurah} of ${pageData.ayahs[currentIndex]?.surah?.englishName}` : 'Ready to play'}
                    </div>
                    
                    <div className="flex items-center gap-4">
                        <Button variant="outline" size="icon" onClick={playPrevious} disabled={currentIndex <= 0}>
                            <SkipBack className="h-4 w-4" />
                        </Button>
                        
                        <Button 
                            className="h-12 w-12 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white" 
                            onClick={togglePlayPause}
                        >
                            {isPlaying ? <Pause className="h-6 w-6" /> : <Play className="h-6 w-6 ml-1" />}
                        </Button>
                        
                        <Button variant="outline" size="icon" onClick={playNext} disabled={currentIndex >= ayahsCount - 1}>
                            <SkipForward className="h-4 w-4" />
                        </Button>
                    </div>
                    <div className="w-24"></div> {/* Spacer for balance */}
                </div>
            </div>

            {/* Edit Warning Modal */}
            <Dialog open={editModalOpen} onOpenChange={setEditModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-red-600">
                            <AlertTriangle className="h-5 w-5" />
                            Sensitive Edit Warning
                        </DialogTitle>
                        <DialogDescription className="text-red-600 font-medium">
                            CAUTION: You are about to edit the text of the Holy Quran or its translation. Any typo or error here is highly sensitive. Please double-check your changes before saving.
                        </DialogDescription>
                    </DialogHeader>
                    
                    <div className="my-4">
                        <label className="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Ayah Text
                        </label>
                        <Textarea 
                            rows={6}
                            value={editText}
                            onChange={(e) => setEditText(e.target.value)}
                            className="w-full"
                        />
                    </div>
                    
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setEditModalOpen(false)}>Cancel</Button>
                        <Button variant="destructive" onClick={saveEdit}>Confirm & Save</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
