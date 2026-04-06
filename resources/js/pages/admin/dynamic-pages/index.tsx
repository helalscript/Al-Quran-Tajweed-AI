import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
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
import { ArrowDown, ArrowUp, ArrowUpDown, Search } from 'lucide-react';
import { useState } from 'react';

interface ColumnMeta {
    label: string;
    type?: 'status';
    visible?: boolean;
}

interface DynamicPageItem {
    id: number;
    page_title: string;
    page_slug: string;
    status: string;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    title: string;
    resource: string;
    columns: Record<string, ColumnMeta>;
    items: Paginated<DynamicPageItem>;
    filters: {
        search?: string;
        status?: string;
        per_page?: number;
        sort_col?: string;
        sort_dir?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Dynamic Pages', href: '/admin/pages' },
];

export default function Index({ title, columns, items, filters }: Props) {
    const [searchValue, setSearchValue] = useState(filters.search ?? '');

    const handleFilterChange = (partial: {
        search?: string;
        status?: string;
        per_page?: number;
        sort_col?: string;
        sort_dir?: string;
    }) => {
        router.get(
            '/admin/pages',
            {
                search: partial.search ?? filters.search ?? '',
                status: partial.status ?? filters.status ?? '',
                per_page: partial.per_page ?? filters.per_page ?? 10,
                sort_col: partial.sort_col ?? filters.sort_col ?? '',
                sort_dir: partial.sort_dir ?? filters.sort_dir ?? '',
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const toggleSort = (col: string) => {
        const currentCol = filters.sort_col ?? '';
        const currentDir = (filters.sort_dir ?? '').toLowerCase();

        if (currentCol !== col) {
            handleFilterChange({ sort_col: col, sort_dir: 'asc' });
            return;
        }

        handleFilterChange({
            sort_col: col,
            sort_dir: currentDir === 'asc' ? 'desc' : 'asc',
        });
    };

    const SortIcon = ({ col }: { col: string }) => {
        if ((filters.sort_col ?? '') !== col) {
            return <ArrowUpDown className="size-3.5 opacity-60" />;
        }

        return (filters.sort_dir ?? '').toLowerCase() === 'desc' ? (
            <ArrowDown className="size-3.5" />
        ) : (
            <ArrowUp className="size-3.5" />
        );
    };

    const handleToggleStatus = (id: number) => {
        router.patch(
            `/admin/pages/${id}/toggle`,
            {},
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const handleDelete = (id: number) => {
        if (!confirm('Are you sure you want to delete this page?')) {
            return;
        }
        router.delete(`/admin/pages/${id}`, {
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
                    <div className="flex w-full flex-col gap-3 md:flex-row md:items-center">
                        <div className="w-full md:w-1/3">
                            <InputGroup>
                                <InputGroupInput
                                    placeholder="Search pages..."
                                    value={searchValue}
                                    onChange={(e) => {
                                        const value = e.target.value;
                                        setSearchValue(value);
                                        handleFilterChange({ search: value });
                                    }}
                                />
                                <InputGroupAddon>
                                    <Search className="size-4" />
                                </InputGroupAddon>
                            </InputGroup>
                        </div>

                        <div className="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-center">
                            <select
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:w-40"
                                value={filters.status ?? ''}
                                onChange={(e) =>
                                    handleFilterChange({
                                        status: e.target.value,
                                    })
                                }
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>

                            <select
                                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:w-28"
                                value={filters.per_page ?? 10}
                                onChange={(e) =>
                                    handleFilterChange({
                                        per_page: Number(e.target.value) || 10,
                                    })
                                }
                            >
                                <option value={10}>10 / page</option>
                                <option value={25}>25 / page</option>
                                <option value={50}>50 / page</option>
                            </select>
                        </div>
                    </div>

                    <div className="text-right">
                        <Link href="/admin/pages/create">
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
                                    <TableHead key={key}>
                                        <div className="flex items-center gap-2">
                                            <span>{meta.label}</span>
                                            {(key === 'page_title' ||
                                                key === 'page_slug') && (
                                                    <button
                                                        type="button"
                                                        className="inline-flex items-center gap-1 rounded px-1 py-0.5 text-xs text-neutral-600 hover:bg-neutral-200/60 dark:text-neutral-300 dark:hover:bg-neutral-700/60"
                                                        onClick={() =>
                                                            toggleSort(key)
                                                        }
                                                    >
                                                        <SortIcon col={key} />
                                                        <span className="sr-only">
                                                            Sort
                                                        </span>
                                                    </button>
                                                )}
                                        </div>
                                    </TableHead>
                                ))}
                                <TableHead className="w-[210px]">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {items.data.length > 0 ? (
                                items.data.map((page, index) => (
                                    <TableRow key={page.id}>
                                        <TableCell>
                                            {(items.current_page - 1) *
                                                (filters.per_page ?? 10) +
                                                index +
                                                1}
                                        </TableCell>

                                        {visibleColumns.map(([key, meta]) => {
                                            const value = (page as unknown as Record<string, unknown>)[key];

                                            if (meta.type === 'status') {
                                                const isActive =
                                                    value === 'active';
                                                return (
                                                    <TableCell key={key}>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                handleToggleStatus(
                                                                    page.id,
                                                                )
                                                            }
                                                            className={`relative inline-flex h-6 w-12 items-center rounded-full border border-transparent text-xs font-medium transition-colors ${isActive
                                                                    ? 'bg-emerald-500'
                                                                    : 'bg-neutral-400'
                                                                }`}
                                                        >
                                                            <span
                                                                className={`inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform ${isActive
                                                                        ? 'translate-x-6'
                                                                        : 'translate-x-1'
                                                                    }`}
                                                            />
                                                        </button>
                                                    </TableCell>
                                                );
                                            }

                                            return (
                                                <TableCell key={key}>
                                                    {String(value ?? '')}
                                                </TableCell>
                                            );
                                        })}

                                        <TableCell>
                                            <div className="flex flex-wrap gap-2">
                                                <Link
                                                    href={`/page/${page.page_slug}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                    >
                                                        View
                                                    </Button>
                                                </Link>
                                                <Link
                                                    href={`/admin/pages/${page.id}/edit`}
                                                >
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
                                                    onClick={() =>
                                                        handleDelete(page.id)
                                                    }
                                                >
                                                    Delete
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell
                                        colSpan={visibleColumns.length + 2}
                                    >
                                        <div className="py-6 text-center text-sm text-neutral-500">
                                            No pages found.
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

