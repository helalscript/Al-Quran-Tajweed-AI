import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Subscription {
    id: number;
    user_id: number;
    rc_original_app_user_id: string | null;
    product_id: string | null;
    entitlement_id: string | null;
    status: string;
    expires_at: string | null;
    purchased_at: string | null;
    created_at: string;
    user: User;
}

interface Props {
    subscription: Subscription;
}

export default function Show({ subscription }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Subscriptions', href: '/admin/subscriptions' },
        { title: `Subscription #${subscription.id}`, href: `/admin/subscriptions/${subscription.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Subscription #${subscription.id}`} />

            <div className="m-4 space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Subscription Details</h1>
                    <Link href="/admin/subscriptions">
                        <Button variant="outline" size="sm" className="gap-2">
                            <ArrowLeft className="h-4 w-4" />
                            Back to Subscriptions
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card className="p-6">
                        <h2 className="text-lg font-semibold mb-4">Subscription Info</h2>
                        <dl className="space-y-4">
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">Status</dt>
                                <dd className="mt-1">
                                    <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                        subscription.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                        subscription.status === 'canceled' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' :
                                        'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-400'
                                    }`}>
                                        {subscription.status.toUpperCase()}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">Product ID</dt>
                                <dd className="mt-1 text-sm">{subscription.product_id ?? '-'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">Entitlement ID</dt>
                                <dd className="mt-1 text-sm">{subscription.entitlement_id ?? '-'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">RevenueCat User ID</dt>
                                <dd className="mt-1 text-sm font-mono break-all">{subscription.rc_original_app_user_id ?? '-'}</dd>
                            </div>
                        </dl>
                    </Card>

                    <Card className="p-6">
                        <h2 className="text-lg font-semibold mb-4">Dates & User Info</h2>
                        <dl className="space-y-4">
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">User</dt>
                                <dd className="mt-1 text-sm">
                                    <div className="flex flex-col">
                                        <span className="font-medium">{subscription.user?.name ?? 'Unknown'}</span>
                                        <span className="text-neutral-500">{subscription.user?.email ?? ''}</span>
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">Purchased At</dt>
                                <dd className="mt-1 text-sm">
                                    {subscription.purchased_at ? new Date(subscription.purchased_at).toLocaleString() : '-'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">Expires At</dt>
                                <dd className="mt-1 text-sm">
                                    {subscription.expires_at ? new Date(subscription.expires_at).toLocaleString() : '-'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-neutral-500">Created At</dt>
                                <dd className="mt-1 text-sm">
                                    {subscription.created_at ? new Date(subscription.created_at).toLocaleString() : '-'}
                                </dd>
                            </div>
                        </dl>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
