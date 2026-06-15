import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';

const ACCEPT_IMAGE = 'image/jpeg,image/png,image/webp,image/gif';
const MAX_SIZE_MB = 2;

interface Category {
    id: number;
    name: string;
    type: string;
    order: number;
    status: string;
    image?: string | null;
}

interface Props {
    category: Category;
}

export default function Edit({ category }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Categories', href: '/admin/categories' },
        { title: 'Edit', href: `/admin/categories/${category.id}/edit` },
    ];

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [dragActive, setDragActive] = useState(false);
    const normalizeImage = (src: string | null): string | null => {
        if (!src) return null;
        if (src.startsWith('http')) return src;
        return src.startsWith('/') ? src : `/${src}`;
    };

    const [existingImage, setExistingImage] = useState<string | null>(
        normalizeImage(category.image),
    );

    const { data, setData, post, processing, errors } = useForm<{
        name: string;
        type: string;
        order: number;
        status: string;
        image: File | null;
        _method: string;
    }>({
        name: category.name || '',
        type: category.type || '',
        order: category.order || 0,
        status: category.status || 'active',
        image: null,
        _method: 'PUT',
    });

    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    useEffect(() => {
        if (data.image instanceof File) {
            const url = URL.createObjectURL(data.image);
            setPreviewUrl(url);
            return () => URL.revokeObjectURL(url);
        }
        setPreviewUrl(null);
    }, [data.image]);

    const handleFile = (file: File | null) => {
        if (!file) {
            setData('image', null);
            return;
        }
        if (!file.type.startsWith('image/')) {
            return;
        }
        if (file.size > MAX_SIZE_MB * 1024 * 1024) {
            return;
        }
        setData('image', file);
        setExistingImage(null);
    };

    const onDrag = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(true);
    };

    const onDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
    };

    const onDrop = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
        const file = e.dataTransfer.files?.[0];
        handleFile(file ?? null);
    };

    const onFileInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] ?? null;
        handleFile(file);
        e.target.value = '';
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/categories/${category.id}`, {
            forceFormData: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Category" />
            <div className="p-4">
                <Card className="w-full space-y-4 p-6">
                    <div className="mb-6 flex items-center justify-between">
                        <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                            Edit Category
                        </h2>
                        <Link href="/admin/categories">
                            <Button variant="outline">Back to List</Button>
                        </Link>
                    </div>

                    <form onSubmit={submit} className="space-y-6">
                        <div>
                            <Label htmlFor="name">Name</Label>
                            <Input
                                id="name"
                                type="text"
                                className="mt-1 block w-full"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                            />
                            <InputError message={errors.name} className="mt-2" />
                        </div>

                        <div>
                            <Label htmlFor="type">Type</Label>
                            <select
                                id="type"
                                className="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                value={data.type}
                                onChange={(e) => setData('type', e.target.value)}
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="dua">Dua</option>
                                <option value="quran">Quran</option>
                                <option value="prayer">Prayer</option>
                            </select>
                            <InputError message={errors.type} className="mt-2" />
                        </div>

                        <div className="space-y-1.5">
                            <Label>Image</Label>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept={ACCEPT_IMAGE}
                                className="sr-only"
                                onChange={onFileInputChange}
                            />
                            <div
                                onDragEnter={onDrag}
                                onDragOver={onDrag}
                                onDragLeave={onDragLeave}
                                onDrop={onDrop}
                                onClick={() => fileInputRef.current?.click()}
                                className={`flex min-h-[160px] cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed p-4 text-center transition-colors ${
                                    dragActive
                                        ? 'border-primary bg-primary/5'
                                        : 'border-input hover:border-primary/50 hover:bg-muted/50'
                                }`}
                            >
                                {previewUrl || existingImage ? (
                                    <img
                                        src={previewUrl ?? existingImage ?? ''}
                                        alt="Category Preview"
                                        className="mx-auto max-h-48 max-w-full rounded object-contain"
                                    />
                                ) : (
                                    <>
                                        <p className="text-sm text-muted-foreground">
                                            Drag and drop an image here, or
                                            click to select
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            JPEG, PNG, WebP, GIF (max {MAX_SIZE_MB}
                                            MB)
                                        </p>
                                    </>
                                )}
                            </div>
                            <InputError message={errors.image} className="mt-2" />
                        </div>

                        <div>
                            <Label htmlFor="order">Order</Label>
                            <Input
                                id="order"
                                type="number"
                                className="mt-1 block w-full"
                                value={data.order}
                                onChange={(e) => setData('order', Number(e.target.value))}
                                required
                            />
                            <InputError message={errors.order} className="mt-2" />
                        </div>

                        <div>
                            <Label htmlFor="status">Status</Label>
                            <select
                                id="status"
                                className="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                required
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputError message={errors.status} className="mt-2" />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Update Category</Button>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
