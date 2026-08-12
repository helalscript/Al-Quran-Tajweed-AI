import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
} from '@/components/ui/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Search } from 'lucide-react';

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

interface PaginatedData {
    data: Subscription[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

interface Props {
    subscriptions: PaginatedData;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Subscriptions', href: '/admin/subscriptions' },
];

export default function Index({ subscriptions }: Props) {
    const [statusFilter, setStatusFilter] = useState('');

    const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const status = e.target.value;
        setStatusFilter(status);
        
        router.get(
            '/admin/subscriptions',
            { status },
            { preserveState: true, replace: true }
        );
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this subscription?')) {
            router.delete(`/admin/subscriptions/${id}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Subscriptions" />

            <Card className="m-4 space-y-4 p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex w-full flex-col gap-3 md:flex-row md:items-center">
                        <div className="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-center">
                            <select
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:w-40"
                                value={statusFilter}
                                onChange={handleFilterChange}
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="canceled">Canceled</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div className="mx-2 overflow-x-auto">
                    <Table className="w-full">
                        <TableHeader className="variant-secondary m-4 bg-gray-100 dark:bg-gray-800">
                            <TableRow>
                                <TableHead className="w-[50px]">#</TableHead>
                                <TableHead>User</TableHead>
                                <TableHead>Product ID</TableHead>
                                <TableHead>Entitlement</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Purchased At</TableHead>
                                <TableHead>Expires At</TableHead>
                                <TableHead className="w-[200px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {subscriptions.data.length > 0 ? (
                                subscriptions.data.map((subscription, index) => (
                                    <TableRow key={subscription.id}>
                                        <TableCell>
                                            {(subscriptions.current_page - 1) * subscriptions.per_page + index + 1}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="font-medium">{subscription.user?.name ?? 'Unknown'}</span>
                                                <span className="text-xs text-neutral-500">{subscription.user?.email ?? ''}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>{subscription.product_id ?? '-'}</TableCell>
                                        <TableCell>{subscription.entitlement_id ?? '-'}</TableCell>
                                        <TableCell>
                                            <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                                subscription.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                                subscription.status === 'canceled' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' :
                                                'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-400'
                                            }`}>
                                                {subscription.status.toUpperCase()}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            {subscription.purchased_at ? new Date(subscription.purchased_at).toLocaleString() : '-'}
                                        </TableCell>
                                        <TableCell>
                                            {subscription.expires_at ? new Date(subscription.expires_at).toLocaleString() : '-'}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={`/admin/subscriptions/${subscription.id}`}>
                                                    <Button size="sm" variant="outline">
                                                        View
                                                    </Button>
                                                </Link>
                                                <Button size="sm" variant="destructive" onClick={() => handleDelete(subscription.id)}>
                                                    Delete
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={8}>
                                        <div className="py-6 text-center text-sm text-neutral-500">
                                            No subscriptions found
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    {subscriptions.last_page > 1 && (
                        <div className="mt-4 flex items-center justify-end">
                            <Pagination>
                                <PaginationContent>
                                    {subscriptions.links.map((link, index) => (
                                        <PaginationItem key={index}>
                                            {link.url ? (
                                                <PaginationLink
                                                    href={link.url}
                                                    isActive={link.active}
                                                    dangerouslySetInnerHTML={{
                                                        __html: link.label,
                                                    }}
                                                />
                                            ) : (
                                                <PaginationEllipsis />
                                            )}
                                        </PaginationItem>
                                    ))}
                                </PaginationContent>
                            </Pagination>
                        </div>
                    )}
                </div>
            </Card>
        </AppLayout>
    );
}
