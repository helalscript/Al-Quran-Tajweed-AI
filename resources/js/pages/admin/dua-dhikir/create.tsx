import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ImagePlus, Plus, Trash2, X } from 'lucide-react';
import { useRef, useState } from 'react';

interface Category {
    id: number;
    name: string;
    type: string;
}

interface Translation {
    language_code: string;
    title: string;
    translation: string;
    notes: string;
    benefits: string;
    fawaid: string;
}

interface Props {
    categories: Category[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Dua & Dhikir', href: '/admin/dua-dhikir' },
    { title: 'Create', href: '/admin/dua-dhikir/create' },
];

const SELECT_CLASS =
    'w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

const TEXTAREA_CLASS =
    'w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 min-h-[100px] resize-y';

// List of supported languages for the dropdown
const LANGUAGES = [
    { code: 'en', name: 'English (en)' },
    { code: 'bn', name: 'Bengali (bn)' },
    { code: 'id', name: 'Indonesian (id)' },
    { code: 'ar', name: 'Arabic (ar)' },
    { code: 'latin', name: 'Latin (transliteration)' }, // Special language code for latin
];

export default function Create({ categories }: Props) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [imagePreview, setImagePreview] = useState<string | null>(null);

    const { data, setData, post, processing, errors } = useForm<{
        category_id: string;
        arabic: string;
        source: string;
        image: File | null;
        audio_url: string;
        order: string;
        status: string;
        translations: Translation[];
    }>({
        category_id: '',
        arabic: '',
        source: '',
        image: null,
        audio_url: '',
        order: '0',
        status: 'active',
        translations: [
            {
                language_code: 'en',
                title: '',
                translation: '',
                notes: '',
                benefits: '',
                fawaid: '',
            }
        ],
    });

    function handleImageChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0] ?? null;
        setData('image', file);
        if (file) {
            setImagePreview(URL.createObjectURL(file));
        } else {
            setImagePreview(null);
        }
    }

    function removeImage() {
        setData('image', null);
        setImagePreview(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    }

    function addTranslation() {
        setData('translations', [
            ...data.translations,
            { language_code: 'bn', title: '', translation: '', notes: '', benefits: '', fawaid: '' }
        ]);
    }

    function removeTranslation(index: number) {
        const newTranslations = [...data.translations];
        newTranslations.splice(index, 1);
        setData('translations', newTranslations);
    }

    function updateTranslation(index: number, field: keyof Translation, value: string) {
        const newTranslations = [...data.translations];
        newTranslations[index][field] = value;
        setData('translations', newTranslations);
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/admin/dua-dhikir', {
            forceFormData: true,
            preserveScroll: true,
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Dua & Dhikir" />

            <div className="p-4">
                <form onSubmit={submit} className="space-y-6" encType="multipart/form-data">
                    <Card className="w-full space-y-4 p-6">
                        <div className="flex items-center justify-between border-b pb-4">
                            <h1 className="text-2xl font-semibold">Base Dua Details (Universal)</h1>
                        </div>

                        {/* ── Row 1: Category + Status ── */}
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="category_id">Category <span className="text-red-500">*</span></Label>
                                <select
                                    id="category_id"
                                    className={SELECT_CLASS}
                                    value={data.category_id}
                                    onChange={(e) => setData('category_id', e.target.value)}
                                >
                                    <option value="">— Select Category —</option>
                                    {categories.map((c) => (
                                        <option key={c.id} value={c.id}>{c.name}</option>
                                    ))}
                                </select>
                                {errors.category_id && <p className="text-sm text-red-500">{errors.category_id}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    className={SELECT_CLASS}
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                {errors.status && <p className="text-sm text-red-500">{errors.status}</p>}
                            </div>
                        </div>

                        {/* ── Arabic ── */}
                        <div className="space-y-1.5">
                            <Label htmlFor="arabic">Arabic Text <span className="text-red-500">*</span></Label>
                            <textarea
                                id="arabic"
                                dir="rtl"
                                className={TEXTAREA_CLASS + ' text-right font-arabic text-xl leading-loose'}
                                value={data.arabic}
                                onChange={(e) => setData('arabic', e.target.value)}
                                placeholder="اكتب النص العربي هنا..."
                            />
                            {errors.arabic && <p className="text-sm text-red-500">{errors.arabic}</p>}
                        </div>

                        {/* ── Source ── */}
                        <div className="space-y-1.5">
                            <Label htmlFor="source">Source</Label>
                            <Input
                                id="source"
                                value={data.source}
                                onChange={(e) => setData('source', e.target.value)}
                                placeholder="e.g. Bukhari 1234"
                            />
                            {errors.source && <p className="text-sm text-red-500">{errors.source}</p>}
                        </div>

                        {/* ── Audio URL + Order ── */}
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="audio_url">Audio URL</Label>
                                <Input
                                    id="audio_url"
                                    value={data.audio_url}
                                    onChange={(e) => setData('audio_url', e.target.value)}
                                    placeholder="https://..."
                                />
                                {errors.audio_url && <p className="text-sm text-red-500">{errors.audio_url}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="order">Order</Label>
                                <Input
                                    id="order"
                                    type="number"
                                    min="0"
                                    value={data.order}
                                    onChange={(e) => setData('order', e.target.value)}
                                />
                                {errors.order && <p className="text-sm text-red-500">{errors.order}</p>}
                            </div>
                        </div>

                        {/* ── Image Upload ── */}
                        <div className="space-y-1.5">
                            <Label>Image</Label>
                            <div className="flex items-start gap-4">
                                {imagePreview ? (
                                    <div className="relative">
                                        <img
                                            src={imagePreview}
                                            alt="Preview"
                                            className="h-32 w-32 rounded-lg object-cover border"
                                        />
                                        <button
                                            type="button"
                                            onClick={removeImage}
                                            className="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                                        >
                                            <X className="h-3 w-3" />
                                        </button>
                                    </div>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={() => fileInputRef.current?.click()}
                                        className="flex h-32 w-32 flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-muted-foreground/30 text-muted-foreground hover:border-primary hover:text-primary transition-colors"
                                    >
                                        <ImagePlus className="h-8 w-8" />
                                        <span className="text-xs">Upload Image</span>
                                    </button>
                                )}
                                <div className="flex-1 space-y-1">
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept="image/jpg,image/jpeg,image/png,image/webp"
                                        className="hidden"
                                        onChange={handleImageChange}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Accepted: JPG, JPEG, PNG, WebP — Max 2 MB
                                    </p>
                                    {!imagePreview && (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => fileInputRef.current?.click()}
                                        >
                                            Choose File
                                        </Button>
                                    )}
                                </div>
                            </div>
                            {errors.image && <p className="text-sm text-red-500">{errors.image}</p>}
                        </div>
                    </Card>

                    {/* Translations Section */}
                    {typeof errors.translations === 'string' && (
                        <p className="text-sm font-semibold text-red-500">
                            {errors.translations}
                        </p>
                    )}

                    <div className="space-y-6">
                        <div className="flex items-center justify-between">
                            <h2 className="text-xl font-semibold">Translations</h2>
                            <Button type="button" onClick={addTranslation} variant="secondary">
                                <Plus className="mr-2 h-4 w-4" /> Add Translation
                            </Button>
                        </div>

                        {data.translations.map((trans, index) => (
                            <Card key={index} className="relative w-full space-y-4 p-6 pt-10">
                                {data.translations.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={() => removeTranslation(index)}
                                        className="absolute top-4 right-4 text-red-500 hover:text-red-700"
                                        title="Remove translation"
                                    >
                                        <Trash2 className="h-5 w-5" />
                                    </button>
                                )}
                                
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="space-y-1.5">
                                        <Label>Language <span className="text-red-500">*</span></Label>
                                        <select
                                            className={SELECT_CLASS}
                                            value={trans.language_code}
                                            onChange={(e) => updateTranslation(index, 'language_code', e.target.value)}
                                        >
                                            {LANGUAGES.map(lang => (
                                                <option key={lang.code} value={lang.code}>{lang.name}</option>
                                            ))}
                                        </select>
                                        {(errors as any)[`translations.${index}.language_code`] && (
                                            <p className="text-sm text-red-500">{(errors as any)[`translations.${index}.language_code`]}</p>
                                        )}
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label>Title <span className="text-red-500">*</span></Label>
                                        <Input
                                            value={trans.title}
                                            onChange={(e) => updateTranslation(index, 'title', e.target.value)}
                                            placeholder="Enter title"
                                        />
                                        {(errors as any)[`translations.${index}.title`] && (
                                            <p className="text-sm text-red-500">{(errors as any)[`translations.${index}.title`]}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-1.5">
                                    <Label>Translation (or Transliteration)</Label>
                                    <textarea
                                        className={TEXTAREA_CLASS}
                                        value={trans.translation}
                                        onChange={(e) => updateTranslation(index, 'translation', e.target.value)}
                                        placeholder="Translation or transliteration..."
                                    />
                                    {(errors as any)[`translations.${index}.translation`] && (
                                        <p className="text-sm text-red-500">{(errors as any)[`translations.${index}.translation`]}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="space-y-1.5">
                                        <Label>Notes</Label>
                                        <textarea
                                            className={TEXTAREA_CLASS}
                                            value={trans.notes}
                                            onChange={(e) => updateTranslation(index, 'notes', e.target.value)}
                                        />
                                        {(errors as any)[`translations.${index}.notes`] && (
                                            <p className="text-sm text-red-500">{(errors as any)[`translations.${index}.notes`]}</p>
                                        )}
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label>Benefits</Label>
                                        <textarea
                                            className={TEXTAREA_CLASS}
                                            value={trans.benefits}
                                            onChange={(e) => updateTranslation(index, 'benefits', e.target.value)}
                                        />
                                        {(errors as any)[`translations.${index}.benefits`] && (
                                            <p className="text-sm text-red-500">{(errors as any)[`translations.${index}.benefits`]}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-1.5">
                                    <Label>Fawaid</Label>
                                    <textarea
                                        className={TEXTAREA_CLASS}
                                        value={trans.fawaid}
                                        onChange={(e) => updateTranslation(index, 'fawaid', e.target.value)}
                                    />
                                    {(errors as any)[`translations.${index}.fawaid`] && (
                                        <p className="text-sm text-red-500">{(errors as any)[`translations.${index}.fawaid`]}</p>
                                    )}
                                </div>
                            </Card>
                        ))}
                    </div>

                    {/* ── Actions ── */}
                    <div className="flex items-center gap-3 py-4">
                        <Button type="submit" size="lg" disabled={processing}>
                            {processing ? 'Saving...' : 'Save All Changes'}
                        </Button>
                        <Link href="/admin/dua-dhikir">
                            <Button type="button" variant="outline" size="lg">Cancel</Button>
                        </Link>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
