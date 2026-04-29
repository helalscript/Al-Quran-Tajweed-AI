import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface Category {
    id: number;
    name: string;
    type: string;
    order: number;
    status: string;
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

    const { data, setData, put, processing, errors } = useForm({
        name: category.name || '',
        type: category.type || '',
        order: category.order || 0,
        status: category.status || 'active',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/admin/categories/${category.id}`);
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
