import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationLink } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';

interface ColumnMeta {
    label: string;
    type?: 'status' | 'image';
    visible?: boolean;
}

interface DuaDhikirItem {
    id: number;
    title: string;
    category?: string | null;
    image?: string | null;
    status: string;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Category {
    id: number;
    name: string;
}

interface Props {
    title: string;
    resource: string;
    columns: Record<string, ColumnMeta>;
    items: Paginated<DuaDhikirItem>;
    categories: Category[];
    filters: {
        search?: string;
        status?: string;
        category_id?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Dua & Dhikir', href: '/admin/dua-dhikir' },
];

export default function DuaDhikirIndex({
    title,
    columns,
    items,
    categories,
    filters,
}: Props) {
    const applyFilters = (newFilters: Record<string, string | undefined>) => {
        router.get(
            '/admin/dua-dhikir',
            { ...filters, ...newFilters },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchChange = (value: string) => applyFilters({ search: value });
    const handleCategoryChange = (value: string) => applyFilters({ category_id: value });
    const handleStatusChange = (value: string) => applyFilters({ status: value });

    const handleToggleStatus = (id: number) => {
        router.patch(
            `/admin/dua-dhikir/${id}/toggle`,
            {},
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const handleDelete = (id: number) => {
        if (!confirm('Are you sure you want to delete this item?')) {
            return;
        }

        router.delete(`/admin/dua-dhikir/${id}`, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const visibleColumns = Object.entries(columns).filter(
        ([, meta]) => meta.visible !== false,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />

            <Card className="m-4 space-y-4 p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex w-full flex-col gap-4 md:w-2/3 md:flex-row">
                        <div className="md:w-1/2">
                            <InputGroup>
                                <InputGroupInput
                                    placeholder="Search..."
                                    defaultValue={filters.search}
                                    onBlur={(e) => handleSearchChange(e.target.value)}
                                />
                                <InputGroupAddon>
                                    <Search className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                        </div>
                        <div className="md:w-1/4">
                            <select
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                value={filters.category_id || ''}
                                onChange={(e) => handleCategoryChange(e.target.value)}
                            >
                                <option value="">All Categories</option>
                                {categories?.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                        <div className="md:w-1/4">
                            <select
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                value={filters.status || ''}
                                onChange={(e) => handleStatusChange(e.target.value)}
                            >
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div className="text-right">
                        <Link href="/admin/dua-dhikir/create">
                            <Button>Add New</Button>
                        </Link>
                    </div>
                </div>

                <div className="mx-2">
                    <Table className="w-full">
                        <TableHeader className="variant-secondary m-4 bg-gray-100 dark:bg-gray-800">
                            <TableRow>
                                <TableHead className="w-[50px]">#</TableHead>
                                {visibleColumns.map(([key, meta]) => (
                                    <TableHead key={key}>{meta.label}</TableHead>
                                ))}
                                <TableHead className="w-[200px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {items.data.length > 0 ? (
                                items.data.map((item, index) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            {(items.current_page - 1) * 10 + index + 1}
                                        </TableCell>

                                        {visibleColumns.map(([key, meta]) => {
                                            const value = (item as any)[key];

                                            if (meta.type === 'image') {
                                                return (
                                                    <TableCell key={key}>
                                                        {value ? (
                                                            <img
                                                                src={value}
                                                                alt={item.title}
                                                                className="h-12 w-12 rounded object-cover"
                                                            />
                                                        ) : (
                                                            <span className="text-xs text-neutral-400">
                                                                No image
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                );
                                            }

                                            if (meta.type === 'status') {
                                                return (
                                                    <TableCell key={key}>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                handleToggleStatus(item.id)
                                                            }
                                                            className={`rounded-full px-3 py-1 text-xs font-medium text-white ${
                                                                value === 'active'
                                                                    ? 'bg-green-500'
                                                                    : 'bg-red-500'
                                                            }`}
                                                        >
                                                            {String(value).toUpperCase()}
                                                        </button>
                                                    </TableCell>
                                                );
                                            }

                                            return (
                                                <TableCell key={key}>{value as any}</TableCell>
                                            );
                                        })}

                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={`/admin/dua-dhikir/${item.id}`}>
                                                    <Button
                                                        size="sm"
                                                        variant="secondary"
                                                    >
                                                        Show
                                                    </Button>
                                                </Link>
                                                <Link href={`/admin/dua-dhikir/${item.id}/edit`}>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                    >
                                                        Edit
                                                    </Button>
                                                </Link>
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() => handleDelete(item.id)}
                                                >
                                                    Delete
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={visibleColumns.length + 2}>
                                        <div className="py-6 text-center text-sm text-neutral-500">
                                            No data available
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    <div className="mt-4 flex items-center justify-end">
                        <Pagination>
                            <PaginationContent>
                                {items.links.map((link, index) => (
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
                </div>
            </Card>
        </AppLayout>
    );
}

