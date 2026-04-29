import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Translation {
    id: number;
    language_code: string;
    title: string;
    translation: string | null;
    notes: string | null;
    benefits: string | null;
    fawaid: string | null;
}

interface DuaDhikirItem {
    id: number;
    category?: string | null;
    arabic: string;
    source?: string | null;
    image_url?: string | null;
    audio_url?: string | null;
    order: number;
    status: string;
}

interface Props {
    item: DuaDhikirItem;
    translations: Translation[];
}

export default function Show({ item, translations }: Props) {
    const mainTitle = translations.find(t => t.language_code === 'en')?.title 
                   || translations[0]?.title 
                   || 'No Title';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Dua & Dhikir', href: '/admin/dua-dhikir' },
        { title: mainTitle, href: `/admin/dua-dhikir/${item.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Show: ${mainTitle}`} />

            <div className="p-4">
                <Card className="mx-auto w-full max-w-5xl space-y-6 p-6">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold">{mainTitle}</h1>
                        <div className="flex gap-2">
                            <Link href={`/admin/dua-dhikir/${item.id}/edit`}>
                                <Button variant="outline">Edit</Button>
                            </Link>
                            <Link href="/admin/dua-dhikir">
                                <Button variant="secondary">Back to List</Button>
                            </Link>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div className="md:col-span-2 space-y-6">
                            <section className="space-y-4 rounded-lg border p-4">
                                <h3 className="font-medium text-lg border-b pb-2">Universal Content</h3>
                                <div>
                                    <h4 className="text-sm font-semibold text-muted-foreground mb-1">Arabic</h4>
                                    <p className="text-2xl font-arabic text-right leading-loose" dir="rtl">{item.arabic}</p>
                                </div>
                            </section>

                            <h3 className="font-medium text-xl mt-8">Translations</h3>
                            
                            {translations.map((trans) => (
                                <section key={trans.id} className="space-y-4 rounded-lg border p-4 bg-muted/20">
                                    <div className="flex items-center gap-2 border-b pb-2">
                                        <h3 className="font-medium text-lg">{trans.title}</h3>
                                        <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">
                                            {trans.language_code.toUpperCase()}
                                        </span>
                                    </div>
                                    
                                    {trans.translation && (
                                        <div className="pt-2">
                                            <h4 className="text-sm font-semibold text-muted-foreground mb-1">
                                                {trans.language_code === 'latin' ? 'Transliteration' : 'Translation'}
                                            </h4>
                                            <p className="whitespace-pre-wrap">{trans.translation}</p>
                                        </div>
                                    )}

                                    {trans.notes && (
                                        <div className="pt-2">
                                            <h4 className="text-sm font-semibold text-muted-foreground mb-1">Notes</h4>
                                            <p className="whitespace-pre-wrap">{trans.notes}</p>
                                        </div>
                                    )}
                                    {trans.benefits && (
                                        <div className="pt-2">
                                            <h4 className="text-sm font-semibold text-muted-foreground mb-1">Benefits</h4>
                                            <p className="whitespace-pre-wrap">{trans.benefits}</p>
                                        </div>
                                    )}
                                    {trans.fawaid && (
                                        <div className="pt-2">
                                            <h4 className="text-sm font-semibold text-muted-foreground mb-1">Fawaid</h4>
                                            <p className="whitespace-pre-wrap">{trans.fawaid}</p>
                                        </div>
                                    )}
                                </section>
                            ))}
                        </div>

                        <div className="space-y-6">
                            <section className="space-y-4 rounded-lg border p-4">
                                <h3 className="font-medium text-lg border-b pb-2">Details</h3>
                                <dl className="space-y-3 text-sm">
                                    <div>
                                        <dt className="text-muted-foreground">Category</dt>
                                        <dd className="font-medium">{item.category || 'N/A'}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Status</dt>
                                        <dd>
                                            <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-semibold text-white ${item.status === 'active' ? 'bg-green-500' : 'bg-red-500'}`}>
                                                {item.status.toUpperCase()}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Source</dt>
                                        <dd className="font-medium">{item.source || 'N/A'}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Order</dt>
                                        <dd className="font-medium">{item.order}</dd>
                                    </div>
                                    {item.audio_url && (
                                        <div>
                                            <dt className="text-muted-foreground">Audio</dt>
                                            <dd className="font-medium">
                                                <a href={item.audio_url} target="_blank" rel="noreferrer" className="text-blue-500 hover:underline">Listen</a>
                                            </dd>
                                        </div>
                                    )}
                                </dl>
                            </section>

                            <section className="rounded-lg border p-4">
                                <h3 className="font-medium text-lg border-b pb-2 mb-4">Image</h3>
                                {item.image_url ? (
                                    <div className="rounded-md border overflow-hidden">
                                        <img src={item.image_url} alt={mainTitle} className="w-full object-cover" />
                                    </div>
                                ) : (
                                    <div className="flex h-32 items-center justify-center rounded-md border border-dashed bg-muted/50 text-muted-foreground">
                                        No image available
                                    </div>
                                )}
                            </section>
                        </div>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
