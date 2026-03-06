import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RichTextEditor } from '@/components/rich-text-editor';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';

interface PagePayload {
    id: number;
    page_title: string;
    page_slug: string;
    page_content: string;
    status: string;
}

interface Props {
    page: PagePayload;
}

export default function Edit({ page }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Dynamic Pages', href: '/admin/pages' },
        { title: page.page_title, href: `/admin/pages/${page.id}/edit` },
    ];

    const { data, setData, put, processing, errors } = useForm<{
        page_title: string;
        page_content: string;
        status: string;
    }>({
        page_title: page.page_title,
        page_content: page.page_content,
        status: page.status,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/pages/${page.id}`, {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit: ${page.page_title}`} />

            <div className="p-4">
                <Card className="w-full space-y-4 p-6">
                    <h1 className="text-2xl font-semibold">Edit Page</h1>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-1.5">
                            <Label htmlFor="page_title">Title</Label>
                            <Input
                                id="page_title"
                                value={data.page_title}
                                onChange={(e) =>
                                    setData('page_title', e.target.value)
                                }
                            />
                            {errors.page_title && (
                                <p className="text-sm text-red-500">
                                    {errors.page_title}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Slug</Label>
                            <Input
                                value={page.page_slug}
                                disabled
                                className="bg-muted"
                            />
                            <p className="text-xs text-muted-foreground">
                                Slug is generated from the title and shown in
                                the public URL.
                            </p>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="page_content">Content</Label>
                            <RichTextEditor
                                value={data.page_content}
                                onChange={(html) => setData('page_content', html)}
                                placeholder="Write your page content..."
                            />
                            {errors.page_content && (
                                <p className="text-sm text-red-500">
                                    {errors.page_content}
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
                                {processing ? 'Updating...' : 'Update'}
                            </Button>
                            <Link href="/admin/pages">
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

