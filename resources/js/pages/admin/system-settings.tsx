import { Head, useForm } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

interface SystemSetting {
    paypal_client_id?: string | null;
    paypal_client_secret?: string | null;
    paypal_mode?: string | null;
    paypal_webhook_id?: string | null;
    stripe_public_key?: string | null;
    stripe_secret_key?: string | null;
    stripe_mode?: string | null;
    stripe_webhook_secret?: string | null;
    ai_api_key?: string | null;
    revenuecat_api_key?: string | null;
    revenuecat_project_id?: string | null;
    revenuecat_webhook_secret?: string | null;
    smtp_host?: string | null;
    smtp_port?: number | null;
    smtp_username?: string | null;
    smtp_password?: string | null;
    smtp_encryption?: string | null;
    smtp_from_address?: string | null;
    smtp_from_name?: string | null;
}

interface Props {
    setting: SystemSetting;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'System settings',
        href: '/settings/system',
    },
];

type TabType = 'paypal' | 'stripe' | 'api' | 'smtp' | 'revenuecat';

export default function SystemSettingsPage({ setting }: Props) {
    const [activeTab, setActiveTab] = useState<TabType>('paypal');
    const { data, setData, put, processing } = useForm({
        paypal_client_id: setting.paypal_client_id ?? '',
        paypal_client_secret: setting.paypal_client_secret ?? '',
        paypal_mode: setting.paypal_mode ?? 'sandbox',
        paypal_webhook_id: setting.paypal_webhook_id ?? '',
        stripe_public_key: setting.stripe_public_key ?? '',
        stripe_secret_key: setting.stripe_secret_key ?? '',
        stripe_mode: setting.stripe_mode ?? 'test',
        stripe_webhook_secret: setting.stripe_webhook_secret ?? '',
        ai_api_key: setting.ai_api_key ?? '',
        revenuecat_api_key: setting.revenuecat_api_key ?? '',
        revenuecat_project_id: setting.revenuecat_project_id ?? '',
        revenuecat_webhook_secret: setting.revenuecat_webhook_secret ?? '',
        smtp_host: setting.smtp_host ?? '',
        smtp_port: setting.smtp_port ?? '',
        smtp_username: setting.smtp_username ?? '',
        smtp_password: setting.smtp_password ?? '',
        smtp_encryption: setting.smtp_encryption ?? '',
        smtp_from_address: setting.smtp_from_address ?? '',
        smtp_from_name: setting.smtp_from_name ?? '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/settings/system');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="System settings" />

            <div className="space-y-6 p-4">
                <h1 className="text-2xl font-semibold">System settings</h1>
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    Configure payment gateways, API integrations, and SMTP mail settings.
                </p>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="inline-flex flex-wrap gap-2 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
                        <button
                            type="button"
                            className={`rounded-md px-3.5 py-1.5 text-sm ${
                                activeTab === 'paypal'
                                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60'
                            }`}
                            onClick={() => setActiveTab('paypal')}
                        >
                            PayPal
                        </button>
                        <button
                            type="button"
                            className={`rounded-md px-3.5 py-1.5 text-sm ${
                                activeTab === 'stripe'
                                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60'
                            }`}
                            onClick={() => setActiveTab('stripe')}
                        >
                            Stripe
                        </button>
                        <button
                            type="button"
                            className={`rounded-md px-3.5 py-1.5 text-sm ${
                                activeTab === 'api'
                                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60'
                            }`}
                            onClick={() => setActiveTab('api')}
                        >
                            API Integration
                        </button>
                        <button
                            type="button"
                            className={`rounded-md px-3.5 py-1.5 text-sm ${
                                activeTab === 'revenuecat'
                                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60'
                            }`}
                            onClick={() => setActiveTab('revenuecat')}
                        >
                            RevenueCat
                        </button>
                        <button
                            type="button"
                            className={`rounded-md px-3.5 py-1.5 text-sm ${
                                activeTab === 'smtp'
                                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60'
                            }`}
                            onClick={() => setActiveTab('smtp')}
                        >
                            SMTP Mail
                        </button>
                    </div>

                    {activeTab === 'paypal' && (
                        <Card className="p-6 space-y-4">
                            <h2 className="text-lg font-medium">PayPal configuration</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-1.5">
                                    <Label htmlFor="paypal_client_id">Client ID</Label>
                                    <Input
                                        id="paypal_client_id"
                                        value={data.paypal_client_id}
                                        onChange={(e) => setData('paypal_client_id', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="paypal_client_secret">Client Secret</Label>
                                    <Input
                                        id="paypal_client_secret"
                                        type="password"
                                        value={data.paypal_client_secret}
                                        onChange={(e) => setData('paypal_client_secret', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="paypal_mode">Payment Mode</Label>
                                    <Select
                                        value={data.paypal_mode}
                                        onValueChange={(v) => setData('paypal_mode', v)}
                                    >
                                        <SelectTrigger id="paypal_mode">
                                            <SelectValue placeholder="Select mode" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="sandbox">Sandbox (Test)</SelectItem>
                                            <SelectItem value="live">Live (Production)</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-1.5 md:col-span-2">
                                    <Label htmlFor="paypal_webhook_id">Webhook ID</Label>
                                    <Input
                                        id="paypal_webhook_id"
                                        type="password"
                                        placeholder="Webhook ID from PayPal Developer Dashboard"
                                        value={data.paypal_webhook_id}
                                        onChange={(e) => setData('paypal_webhook_id', e.target.value)}
                                    />
                                </div>
                            </div>
                        </Card>
                    )}

                    {activeTab === 'stripe' && (
                        <Card className="p-6 space-y-4">
                            <h2 className="text-lg font-medium">Stripe configuration</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-1.5">
                                    <Label htmlFor="stripe_public_key">Publishable Key</Label>
                                    <Input
                                        id="stripe_public_key"
                                        placeholder="pk_test_... or pk_live_..."
                                        value={data.stripe_public_key}
                                        onChange={(e) => setData('stripe_public_key', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="stripe_secret_key">Secret Key</Label>
                                    <Input
                                        id="stripe_secret_key"
                                        type="password"
                                        placeholder="sk_test_... or sk_live_..."
                                        value={data.stripe_secret_key}
                                        onChange={(e) => setData('stripe_secret_key', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="stripe_mode">Payment Mode</Label>
                                    <Select
                                        value={data.stripe_mode}
                                        onValueChange={(v) => setData('stripe_mode', v)}
                                    >
                                        <SelectTrigger id="stripe_mode">
                                            <SelectValue placeholder="Select mode" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="test">Test</SelectItem>
                                            <SelectItem value="live">Live (Production)</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-1.5 md:col-span-2">
                                    <Label htmlFor="stripe_webhook_secret">Webhook Secret</Label>
                                    <Input
                                        id="stripe_webhook_secret"
                                        type="password"
                                        placeholder="whsec_..."
                                        value={data.stripe_webhook_secret}
                                        onChange={(e) => setData('stripe_webhook_secret', e.target.value)}
                                    />
                                </div>
                            </div>
                        </Card>
                    )}

                    {activeTab === 'api' && (
                        <Card className="p-6 space-y-4">
                            <h2 className="text-lg font-medium">API integration</h2>

                            <div className="space-y-1.5">
                                <Label htmlFor="ai_api_key">AI API Key</Label>
                                <Input
                                    id="ai_api_key"
                                    type="password"
                                    value={data.ai_api_key}
                                    onChange={(e) => setData('ai_api_key', e.target.value)}
                                />
                            </div>
                        </Card>
                    )}

                    {activeTab === 'revenuecat' && (
                        <Card className="p-6 space-y-4">
                            <h2 className="text-lg font-medium">RevenueCat configuration</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-1.5 md:col-span-2">
                                    <Label htmlFor="revenuecat_api_key">API Key</Label>
                                    <Input
                                        id="revenuecat_api_key"
                                        type="password"
                                        value={data.revenuecat_api_key}
                                        onChange={(e) => setData('revenuecat_api_key', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="revenuecat_project_id">Project ID</Label>
                                    <Input
                                        id="revenuecat_project_id"
                                        value={data.revenuecat_project_id}
                                        onChange={(e) => setData('revenuecat_project_id', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="revenuecat_webhook_secret">Webhook Secret</Label>
                                    <Input
                                        id="revenuecat_webhook_secret"
                                        type="password"
                                        value={data.revenuecat_webhook_secret}
                                        onChange={(e) => setData('revenuecat_webhook_secret', e.target.value)}
                                    />
                                </div>
                            </div>
                        </Card>
                    )}

                    {activeTab === 'smtp' && (
                        <Card className="p-6 space-y-4">
                            <h2 className="text-lg font-medium">SMTP mail configuration</h2>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-1.5">
                                    <Label htmlFor="smtp_host">SMTP Host</Label>
                                    <Input
                                        id="smtp_host"
                                        value={data.smtp_host}
                                        onChange={(e) => setData('smtp_host', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="smtp_port">SMTP Port</Label>
                                    <Input
                                        id="smtp_port"
                                        type="number"
                                        value={data.smtp_port}
                                        onChange={(e) => setData('smtp_port', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="smtp_username">SMTP Username</Label>
                                    <Input
                                        id="smtp_username"
                                        value={data.smtp_username}
                                        onChange={(e) => setData('smtp_username', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="smtp_password">SMTP Password</Label>
                                    <Input
                                        id="smtp_password"
                                        type="password"
                                        value={data.smtp_password}
                                        onChange={(e) => setData('smtp_password', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="smtp_encryption">Encryption</Label>
                                    <Input
                                        id="smtp_encryption"
                                        placeholder="tls / ssl"
                                        value={data.smtp_encryption}
                                        onChange={(e) => setData('smtp_encryption', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="smtp_from_address">From Address</Label>
                                    <Input
                                        id="smtp_from_address"
                                        type="email"
                                        value={data.smtp_from_address}
                                        onChange={(e) => setData('smtp_from_address', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1.5">
                                    <Label htmlFor="smtp_from_name">From Name</Label>
                                    <Input
                                        id="smtp_from_name"
                                        value={data.smtp_from_name}
                                        onChange={(e) => setData('smtp_from_name', e.target.value)}
                                    />
                                </div>
                            </div>
                        </Card>
                    )}

                    <div className="flex justify-end">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save settings'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

