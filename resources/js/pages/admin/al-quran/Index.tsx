import { useState } from 'react';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { History, BookOpen, Layers } from 'lucide-react';

interface Surah {
    number: number;
    name: string;
    english_name: string;
    english_name_translation: string;
    number_of_ayahs: number;
    revelation_type: string;
}

interface HistoryData {
    surah_number: number | null;
    ayah_number: number | null;
    page_number: number | null;
    surah_name: string | null;
    surah_arabic: string | null;
    last_read_at: string;
}

interface Props {
    surahs: Surah[];
    history?: HistoryData | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Al Quran', href: '/admin/al-quran' },
];

export default function Index({ surahs, history }: Props) {
    const [viewMode, setViewMode] = useState<'surah' | 'page'>('surah');
    const pages = Array.from({ length: 604 }, (_, i) => i + 1);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Al Quran - Read" />

            <div className="m-4 mx-auto max-w-7xl">
                {/* Last Reading History */}
                {history && history.surah_number && (
                    <Card className="mb-8 overflow-hidden border-emerald-100 bg-emerald-50/50 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-900/10">
                        <div className="flex flex-col items-center justify-between p-6 sm:flex-row">
                            <div className="flex items-center gap-4">
                                <div className="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-800/50 dark:text-emerald-400">
                                    <History className="h-6 w-6" />
                                </div>
                                <div>
                                    <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Continue Reading</h3>
                                    <p className="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        Surah {history.surah_name} • Ayah {history.ayah_number}
                                    </p>
                                    <p className="text-xs text-gray-400 mt-1">
                                        Last read: {new Date(history.last_read_at).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                            <div className="mt-4 sm:mt-0 flex items-center gap-4">
                                <h3 className="text-2xl text-emerald-600 dark:text-emerald-500" style={{ fontFamily: 'Uthmanic, serif' }}>
                                    {history.surah_arabic}
                                </h3>
                                <Button asChild className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                    <Link href={`/admin/al-quran/${history.surah_number}`}>
                                        Continue
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </Card>
                )}

                {/* View Toggles */}
                <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 className="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                            The Noble Quran
                        </h2>
                        <p className="text-sm text-gray-500">
                            Read by Surah or by Page
                        </p>
                    </div>
                    <div className="flex rounded-md bg-gray-100 p-1 dark:bg-gray-800">
                        <button
                            onClick={() => setViewMode('surah')}
                            className={`flex items-center gap-2 rounded-sm px-4 py-2 text-sm font-medium transition-colors ${viewMode === 'surah' ? 'bg-white text-emerald-600 shadow-sm dark:bg-gray-700 dark:text-emerald-400' : 'text-gray-600 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700/50'}`}
                        >
                            <BookOpen className="h-4 w-4" />
                            Surah View
                        </button>
                        <button
                            onClick={() => setViewMode('page')}
                            className={`flex items-center gap-2 rounded-sm px-4 py-2 text-sm font-medium transition-colors ${viewMode === 'page' ? 'bg-white text-emerald-600 shadow-sm dark:bg-gray-700 dark:text-emerald-400' : 'text-gray-600 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700/50'}`}
                        >
                            <Layers className="h-4 w-4" />
                            Page View
                        </button>
                    </div>
                </div>

                {/* Surah List */}
                {viewMode === 'surah' && (
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {surahs.map((surah) => (
                            <Link key={surah.number} href={`/admin/al-quran/${surah.number}`}>
                                <Card className="flex cursor-pointer items-center justify-between p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <div className="flex items-center gap-4">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <span className="font-bold">{surah.number}</span>
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-800 dark:text-gray-200">
                                                {surah.english_name}
                                            </h3>
                                            <p className="text-xs text-gray-500">
                                                {surah.english_name_translation}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <h3 className="text-lg font-bold text-gray-800 dark:text-gray-200" style={{ fontFamily: 'Uthmanic, serif' }}>
                                            {surah.name}
                                        </h3>
                                        <p className="text-xs text-gray-500">
                                            {surah.number_of_ayahs} Ayahs
                                        </p>
                                    </div>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}

                {/* Page List */}
                {viewMode === 'page' && (
                    <div className="grid grid-cols-3 gap-3 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-12">
                        {pages.map((pageNum) => (
                            <Link key={pageNum} href={`/admin/al-quran/page/${pageNum}`}>
                                <Button 
                                    variant="outline" 
                                    className="w-full font-semibold text-gray-700 hover:border-emerald-500 hover:text-emerald-600 dark:text-gray-300 dark:hover:border-emerald-500 dark:hover:text-emerald-400"
                                >
                                    {pageNum}
                                </Button>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
