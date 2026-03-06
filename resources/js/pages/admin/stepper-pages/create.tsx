import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Stepper Pages', href: admin.stepperPages.index().url },
    { title: 'Create', href: admin.stepperPages.create().url },
];

const ACCEPT_IMAGE = 'image/jpeg,image/png,image/webp,image/gif';
const MAX_SIZE_MB = 2;

export default function Create() {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [dragActive, setDragActive] = useState(false);

    const { data, setData, post, processing, errors } = useForm<{
        title: string;
        description: string;
        image: File | null;
        order_no: number;
        status: string;
    }>({
        title: '',
        description: '',
        image: null,
        order_no: 0,
        status: 'active',
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

    const clearImage = () => {
        setData('image', null);
        fileInputRef.current?.value && (fileInputRef.current.value = '');
    };

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(admin.stepperPages.store().url, {
            forceFormData: true,
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Stepper Page" />

            <div className="p-4">
                <Card className="w-full space-y-4 p-6">
                    <h1 className="text-2xl font-semibold">Create Stepper Page</h1>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="title">Title</Label>
                            <Input
                                id="title"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                            />
                            {errors.title && (
                                <p className="text-sm text-red-500">
                                    {errors.title}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="description">Description</Label>
                            <textarea
                                id="description"
                                className="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                            />
                            {errors.description && (
                                <p className="text-sm text-red-500">
                                    {errors.description}
                                </p>
                            )}
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
                                className={`
                                    flex min-h-[160px] cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed p-4 text-center transition-colors
                                    ${
                                        dragActive
                                            ? 'border-primary bg-primary/5'
                                            : 'border-input hover:border-primary/50 hover:bg-muted/50'
                                    }
                                `}
                            >
                                {previewUrl ? (
                                    <div className="relative w-full">
                                        <img
                                            src={previewUrl}
                                            alt="Preview"
                                            className="mx-auto max-h-48 max-w-full rounded object-contain"
                                        />
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            className="mt-2"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                clearImage();
                                            }}
                                        >
                                            Remove image
                                        </Button>
                                    </div>
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
                            {errors.image && (
                                <p className="text-sm text-red-500">
                                    {errors.image}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="order_no">Order</Label>
                            <Input
                                id="order_no"
                                type="number"
                                value={data.order_no}
                                onChange={(e) =>
                                    setData(
                                        'order_no',
                                        Number(e.target.value) || 0,
                                    )
                                }
                            />
                            {errors.order_no && (
                                <p className="text-sm text-red-500">
                                    {errors.order_no}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="status">Status</Label>
                            <select
                                id="status"
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                value={data.status}
                                onChange={(e) =>
                                    setData('status', e.target.value)
                                }
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            {errors.status && (
                                <p className="text-sm text-red-500">
                                    {errors.status}
                                </p>
                            )}
                        </div>

                        <div className="flex items-center gap-2">
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Saving...' : 'Save'}
                            </Button>
                            <Link href={admin.stepperPages.index().url}>
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                            </Link>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
