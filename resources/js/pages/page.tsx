import AppLogoIcon from '@/components/app-logo-icon';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

interface PageProps {
    page: {
        title: string;
        slug: string;
        content: string;
    };
}

export default function Page({ page }: PageProps) {
    const { name } = usePage<SharedData>().props;
    const year = new Date().getFullYear();

    return (
        <>
            <Head title={page.title} />
            <div className="min-h-screen bg-background text-foreground">
                <header className="border-b border-border/60 bg-background/80 backdrop-blur">
                    <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 md:py-4">
                        <Link
                            href="/"
                            className="flex items-center gap-2 text-sm font-medium"
                        >
                            <span className="flex h-9 w-9 items-center justify-center rounded-md bg-primary text-primary-foreground">
                                <AppLogoIcon className="size-5 fill-current" />
                            </span>
                            <span className="hidden text-sm font-semibold md:inline">
                                {name}
                            </span>
                        </Link>
                        {/* <nav className="flex items-center gap-3 text-xs md:text-sm">
                            <Link
                                href="/login"
                                className="rounded-full border border-border px-3 py-1.5 text-foreground/80 hover:bg-muted"
                            >
                                Admin Login
                            </Link>
                        </nav> */}
                    </div>
                </header>

                <main className="mx-auto flex max-w-3xl flex-col gap-4 px-4 py-10">
                    <div className="rounded-xl border border-border/60 bg-card/80 p-6 shadow-sm">
                        <h1 className="border-b border-border/60 pb-4 text-2xl font-semibold tracking-tight md:text-3xl">
                            {page.title}
                        </h1>

                        <article
                            className="prose max-w-none pt-4 text-sm leading-relaxed prose-headings:mt-6 prose-headings:mb-3 prose-p:mb-3 prose-ul:mb-3 prose-ol:mb-3 dark:prose-invert"
                            dangerouslySetInnerHTML={{ __html: page.content }}
                        />
                    </div>
                </main>

                <footer className="border-t border-border/60 bg-background/80">
                    <div className="mx-auto flex max-w-5xl flex-col items-center justify-between gap-2 px-4 py-4 text-center text-xs text-muted-foreground md:flex-row">
                        <p>
                            © {year} {name}. All rights reserved.
                        </p>
                        <div className="flex gap-3">
                            {/* <Link
                                href="/page/privacy-policy"
                                className="hover:text-foreground"
                            >
                                Privacy Policy
                            </Link> */}
                            <Link
                                href="/page/terms-and-conditions"
                                className="hover:text-foreground"
                            >
                                Terms &amp; Conditions
                            </Link>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}

