import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';
import { type PropsWithChildren, useEffect, useState } from 'react';

const TOAST_DURATION_MS = 5000;

type AlertVariant = 'success' | 'error' | 'warning' | 'info' | 'default';

interface FlashAlert {
    id: number;
    message: string;
    variant: AlertVariant;
    createdAt: number;
}

const toastVariantClasses: Record<
    AlertVariant,
    { root: string; progress: string }
> = {
    success: {
        root: 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950/95 dark:text-green-200',
        progress: 'bg-green-500/40 dark:bg-green-400/40',
    },
    error: {
        root: 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/95 dark:text-red-200',
        progress: 'bg-red-500/40 dark:bg-red-400/40',
    },
    warning: {
        root: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/95 dark:text-amber-200',
        progress: 'bg-amber-500/40 dark:bg-amber-400/40',
    },
    info: {
        root: 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-950/95 dark:text-blue-200',
        progress: 'bg-blue-500/40 dark:bg-blue-400/40',
    },
    default: {
        root: 'border-border bg-background/95 text-foreground dark:bg-background/95 dark:border-border',
        progress: 'bg-muted-foreground/30 dark:bg-muted-foreground/30',
    },
};

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    const { flash } = usePage().props as { flash?: Record<string, string> };
    const [alerts, setAlerts] = useState<FlashAlert[]>([]);

    useEffect(() => {
        if (!flash) {
            return;
        }

        const entries: { variant: AlertVariant; text?: string }[] = [
            { variant: 'success', text: flash.success },
            { variant: 'error', text: flash.error },
            { variant: 'warning', text: flash.warning },
            { variant: 'info', text: flash.info },
            { variant: 'default', text: flash.message },
        ];

        const now = Date.now();
        const incoming = entries
            .filter(({ text }) => text && text.trim() !== '')
            .map(({ variant, text }) => ({
                id: now + Math.floor(Math.random() * 1000),
                message: text as string,
                variant,
                createdAt: now,
            }));

        if (incoming.length === 0) {
            return;
        }

        setAlerts((prev) => {
            const merged = [...prev, ...incoming];
            return merged.slice(-3);
        });
    }, [flash]);

    useEffect(() => {
        if (alerts.length === 0) {
            return;
        }
        const interval = setInterval(() => {
            const now = Date.now();
            setAlerts((prev) =>
                prev.filter(
                    (a) =>
                        a.createdAt != null &&
                        now - a.createdAt < TOAST_DURATION_MS,
                ),
            );
        }, 200);
        return () => clearInterval(interval);
    }, [alerts.length]);

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                {alerts.length > 0 && (
                    <div className="fixed right-4 top-4 z-50 flex w-auto max-w-sm flex-col gap-2">
                        {alerts.map((alert) => {
                            const styles = toastVariantClasses[alert.variant];
                            return (
                                <div
                                    key={alert.id}
                                    role="alert"
                                    className={cn(
                                        'overflow-hidden rounded-lg border px-4 py-3 text-sm shadow-lg backdrop-blur-sm',
                                        styles.root,
                                    )}
                                >
                                    <p className="font-medium tracking-tight">
                                        {alert.message}
                                    </p>
                                    <div
                                        className="mt-2 h-1 w-full overflow-hidden rounded-full"
                                        aria-hidden
                                    >
                                        <div
                                            className={cn(
                                                'h-full rounded-full',
                                                styles.progress,
                                            )}
                                            style={{
                                                animation: `toast-progress ${TOAST_DURATION_MS}ms linear forwards`,
                                            }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
