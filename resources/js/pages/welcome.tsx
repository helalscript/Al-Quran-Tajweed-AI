import { Alert, AlertTitle } from '@/components/ui/alert';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Welcome({ quote }: { quote: string }) {
    const { auth, name } = usePage<SharedData>().props;
    const { flash } = usePage().props as { flash: Record<string, string> };

    // Combine all possible flash messages into one display string
    const message =
        flash.success ||
        flash.error ||
        flash.warning ||
        flash.info ||
        flash.message ||
        "";

    // Determine alert variant based on message type
    const variant = flash.success
        ? "success"
        : flash.error
            ? "error"
            : flash.warning
                ? "warning"
                : flash.info
                    ? "info"
                    : "default";

    // Local state to control visibility
    const [visible, setVisible] = useState(!!message);

    // Custom Cursor State
    const [mousePos, setMousePos] = useState({ x: -100, y: -100 });
    const [isHovering, setIsHovering] = useState(false);
    const [isPressed, setIsPressed] = useState(false);

    useEffect(() => {
        const handleMouseMove = (e: MouseEvent) => {
            setMousePos({ x: e.clientX, y: e.clientY });
            const target = e.target as HTMLElement;
            setIsHovering(!!target.closest('button, a, [role="button"]'));
        };

        const handleMouseDown = () => setIsPressed(true);
        const handleMouseUp = () => setIsPressed(false);

        window.addEventListener('mousemove', handleMouseMove);
        window.addEventListener('mousedown', handleMouseDown);
        window.addEventListener('mouseup', handleMouseUp);
        
        return () => {
            window.removeEventListener('mousemove', handleMouseMove);
            window.removeEventListener('mousedown', handleMouseDown);
            window.removeEventListener('mouseup', handleMouseUp);
        };
    }, []);

    // Hide flash after 3 seconds
    useEffect(() => {
        if (message) {
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 3000);
            return () => clearTimeout(timer);
        }
    }, [message]);

    return (
        <div className="relative min-h-screen flex flex-col items-center justify-center bg-background overflow-hidden selection:bg-primary/30 cursor-none">
            <Head title="Welcome" />

            {/* Pro Interactive Cursor */}
            <div 
                className="fixed top-0 left-0 w-1 h-1 bg-white rounded-full pointer-events-none z-[9999] -translate-x-1/2 -translate-y-1/2 mix-blend-difference transition-transform duration-0 hidden lg:block"
                style={{ left: mousePos.x, top: mousePos.y }}
            />
            <div 
                className={`fixed top-0 left-0 w-12 h-12 border border-white rounded-full pointer-events-none z-[9998] -translate-x-1/2 -translate-y-1/2 mix-blend-difference transition-all duration-[400ms] ease-out hidden lg:block ${
                    isHovering ? 'scale-[1.5] bg-white/10' : 'scale-100'
                } ${isPressed ? 'scale-[0.8]' : ''}`}
                style={{ left: mousePos.x, top: mousePos.y }}
            />

            {/* Premium Animated Background Elements */}
            <div className="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-gradient-to-br from-indigo-500/20 to-transparent rounded-full blur-[120px] animate-pulse duration-[10000ms]" />
            <div className="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-gradient-to-tl from-violet-500/20 to-transparent rounded-full blur-[120px] animate-pulse duration-[8000ms]" />
            
            {/* Mesh Grid Pattern */}
            <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-[0.03] pointer-events-none" />
            <div className="absolute inset-0 bg-[radial-gradient(var(--color-muted-foreground)_1px,transparent_1px)] [background-size:32px_32px] [mask-image:radial-gradient(ellipse_50%_50%_at_50%_50%,#000_70%,transparent_100%)] opacity-[0.1] dark:opacity-[0.05]" />
            
            {/* Premium Background Grid (Dual Mode) */}
            <div className="absolute inset-0 bg-[linear-gradient(to_right,rgba(0,0,0,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,0,0,0.05)_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,rgba(255,255,255,0.1)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-[size:64px_64px] [mask-image:radial-gradient(ellipse_100%_100%_at_50%_50%,#000_40%,transparent_100%)]" />

            {/* Flash Messages */}
            {visible && message && (
                <div className="fixed top-6 right-6 z-50 w-auto max-w-sm transition-all duration-500 animate-in slide-in-from-top-4">
                    <Alert variant={variant} className="shadow-2xl border-none bg-background/80 backdrop-blur-md">
                        <AlertTitle className="text-sm font-semibold">{message}</AlertTitle>
                    </Alert>
                </div>
            )}

            <div className="relative z-10 w-full max-w-2xl px-6 text-center">
                <div className="flex flex-col items-center gap-10">
                    {/* Brand Identity */}
                    <div className="group relative">
                        <div className="absolute -inset-4 rounded-[2rem] bg-gradient-to-r from-indigo-500 to-violet-500 opacity-20 blur-2xl group-hover:opacity-40 transition duration-1000 group-hover:duration-200" />
                        <div className="relative flex h-24 w-24 items-center justify-center rounded-[1.8rem] bg-primary shadow-2xl shadow-primary/30 border border-white/10 overflow-hidden">
                            <AppLogoIcon className="size-14 fill-current text-primary-foreground transform group-hover:scale-110 transition-transform duration-500" />
                            <div className="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                        </div>
                    </div>

                    {/* Typography Section */}
                    <div className="space-y-6 max-w-xl">
                        <h1 className="text-5xl md:text-6xl font-black tracking-tight text-foreground leading-[1.1] animate-in fade-in slide-in-from-bottom-4 duration-700">
                            Welcome to <br />
                            <span className="bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500 bg-[length:200%_auto] bg-clip-text text-transparent animate-gradient">
                                {name}
                            </span>
                        </h1>
                        <p className="text-xl md:text-2xl text-muted-foreground leading-relaxed animate-in fade-in slide-in-from-bottom-6 duration-1000 fill-mode-both italic font-serif">
                            {quote}
                        </p>
                    </div>

                    {/* Primary Call to Action */}
                    <div className="w-full sm:w-auto animate-in fade-in slide-in-from-bottom-8 duration-1000 fill-mode-both delay-300">
                        {auth.user ? (
                            <Link href={dashboard()}>
                                <Button size="lg" className="w-full sm:w-72 h-16 text-lg font-bold rounded-2xl shadow-2xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 active:translate-y-0 transition-all duration-300 group">
                                    <span>Enter Dashboard</span>
                                    <svg className="ml-2 size-5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Button>
                            </Link>
                        ) : (
                            <Link href={login()}>
                                <Button size="lg" className="w-full sm:w-72 h-16 text-lg font-bold rounded-2xl shadow-2xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 active:translate-y-0 transition-all duration-300 group">
                                    <span>Log In to Access</span>
                                    <svg className="ml-2 size-5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </Button>
                            </Link>
                        )}
                    </div>
                </div>
            </div>

            {/* Footer / Metadata */}
            <footer className="absolute bottom-10 left-0 right-0 text-center animate-in fade-in duration-1000 delay-500 fill-mode-both">
                <p className="text-xs tracking-widest uppercase font-bold text-muted-foreground/60 space-x-4">
                    <span>Intuitive</span>
                    <span className="opacity-30">•</span>
                    <span>Powerful</span>
                    <span className="opacity-30">•</span>
                    <span>Modern</span>
                </p>
                <p className="mt-4 text-[10px] font-medium text-muted-foreground/40 uppercase tracking-widest">
                    © {new Date().getFullYear()} {name} Administration
                </p>
            </footer>

            <style dangerouslySetInnerHTML={{ __html: `
                @keyframes gradient {
                    0% { background-position: 0% 50%; }
                    50% { background-position: 100% 50%; }
                    100% { background-position: 0% 50%; }
                }
                .animate-gradient {
                    animation: gradient 6s ease infinite;
                }
            `}} />
        </div>
    );
}
