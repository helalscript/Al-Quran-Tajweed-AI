import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface User {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    avatar?: string | null;
    role?: string | null;
    status?: string | null;
    created_at?: string;
}

interface Props {
    user: User;
}

export default function Show({ user }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Users', href: '/admin/users' },
        { title: user.name, href: `/admin/users/${user.id}` },
    ];

    const avatarSrc =
        user.avatar && (user.avatar.startsWith('http') ? user.avatar : `/${user.avatar}`);

    const joinedAt =
        user.created_at && !Number.isNaN(new Date(user.created_at).getTime())
            ? new Date(user.created_at).toLocaleString()
            : '';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user.name} />

            <div className="p-4">
                <Card className="mx-auto max-w-2xl space-y-6 p-6">
                    <div className="flex items-center gap-4">
                        {avatarSrc ? (
                            <img
                                src={avatarSrc}
                                alt={user.name}
                                className="h-16 w-16 rounded-full object-cover"
                            />
                        ) : (
                            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-neutral-200 text-xl font-semibold text-neutral-700">
                                {user.name?.charAt(0).toUpperCase() ?? '?'}
                            </div>
                        )}

                        <div>
                            <h1 className="text-2xl font-semibold">{user.name}</h1>
                            <p className="text-sm text-neutral-500">{user.email}</p>
                            {user.role && (
                                <p className="text-sm text-neutral-500">Role: {user.role}</p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-3 text-sm">
                        {user.phone && (
                            <div className="flex justify-between">
                                <span className="font-medium text-neutral-600">Phone</span>
                                <span className="text-neutral-800">{user.phone}</span>
                            </div>
                        )}

                        {joinedAt && (
                            <div className="flex justify-between">
                                <span className="font-medium text-neutral-600">Joined</span>
                                <span className="text-neutral-800">{joinedAt}</span>
                            </div>
                        )}

                        {user.status && (
                            <div className="flex justify-between">
                                <span className="font-medium text-neutral-600">Status</span>
                                <span className="text-neutral-800 capitalize">
                                    {user.status}
                                </span>
                            </div>
                        )}
                    </div>

                    <div className="pt-2">
                        <Link
                            href="/admin/users"
                            className="text-sm font-medium text-primary hover:underline"
                        >
                            ← Back to users
                        </Link>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}

