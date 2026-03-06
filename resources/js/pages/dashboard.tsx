import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';

interface StatsProps {
    stats?: {
        users?: {
            total: number;
            active: number;
            inactive: number;
        };
        stepper_pages?: {
            total: number;
            active: number;
        };
    };
    recentUsers?: {
        id: number;
        name: string;
        email: string;
        created_at: string;
    }[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
export default function Dashboard() {
    const { flash, stats, recentUsers } = usePage().props as {
        flash: Record<string, string>;
        stats?: StatsProps['stats'];
        recentUsers?: StatsProps['recentUsers'];
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="mb-2 text-sm font-medium text-neutral-500">
                            Users
                        </h2>
                        <p className="text-3xl font-semibold">
                            {stats?.users?.total ?? 0}
                        </p>
                        <p className="mt-1 text-xs text-neutral-500">
                            Active:{' '}
                            <span className="font-medium">
                                {stats?.users?.active ?? 0}
                            </span>{' '}
                            · Inactive:{' '}
                            <span className="font-medium">
                                {stats?.users?.inactive ?? 0}
                            </span>
                        </p>
                    </div>
                    <div className="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h2 className="mb-2 text-sm font-medium text-neutral-500">
                            Stepper Pages
                        </h2>
                        <p className="text-3xl font-semibold">
                            {stats?.stepper_pages?.total ?? 0}
                        </p>
                        <p className="mt-1 text-xs text-neutral-500">
                            Active:{' '}
                            <span className="font-medium">
                                {stats?.stepper_pages?.active ?? 0}
                            </span>
                        </p>
                    </div>
                    <div className="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/10 dark:stroke-neutral-100/10" />
                        <div className="relative space-y-1">
                            <h2 className="text-sm font-medium text-neutral-500">
                                Quick links
                            </h2>
                            <ul className="text-sm">
                                <li>
                                    <a
                                        href="/admin/stepper-pages"
                                        className="text-primary hover:underline"
                                    >
                                        Manage stepper pages
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/users"
                                        className="text-primary hover:underline"
                                    >
                                        View users
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div className="relative flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <div className="relative h-full w-full bg-background/60 p-4">
                        <h2 className="mb-3 text-sm font-medium text-neutral-500">
                            Recent users
                        </h2>
                        {recentUsers && recentUsers.length > 0 ? (
                            <ul className="space-y-1 text-sm">
                                {recentUsers.map((user) => (
                                    <li
                                        key={user.id}
                                        className="flex items-center justify-between border-b border-border/40 py-1.5 last:border-b-0"
                                    >
                                        <span className="font-medium">
                                            {user.name}
                                        </span>
                                        <span className="text-xs text-neutral-500">
                                            {new Date(
                                                user.created_at,
                                            ).toLocaleDateString()}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-xs text-neutral-500">
                                No recent users.
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
