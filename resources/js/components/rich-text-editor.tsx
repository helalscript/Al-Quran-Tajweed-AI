import { cn } from '@/lib/utils';
import { Bold, Heading1, Heading2, Italic, Link2, List, ListOrdered, Underline } from 'lucide-react';
import React, { useEffect, useId, useMemo, useRef } from 'react';

type BlockType = 'p' | 'h1' | 'h2';

function normalizeHtml(html: string): string {
    const trimmed = html.trim();
    if (trimmed === '' || trimmed === '<br>' || trimmed === '<p><br></p>') {
        return '';
    }
    return html;
}

export interface RichTextEditorProps {
    value: string;
    onChange: (html: string) => void;
    placeholder?: string;
    className?: string;
}

export function RichTextEditor({
    value,
    onChange,
    placeholder = 'Write here...',
    className,
}: RichTextEditorProps) {
    const editorId = useId();
    const editorRef = useRef<HTMLDivElement>(null);
    const lastHtmlRef = useRef<string>('');

    const safeValue = useMemo(() => normalizeHtml(value), [value]);

    useEffect(() => {
        const el = editorRef.current;
        if (!el) return;

        // Avoid cursor jumps: only sync when external value changed.
        if (lastHtmlRef.current === safeValue) {
            return;
        }

        el.innerHTML = safeValue || '';
        lastHtmlRef.current = safeValue;
    }, [safeValue]);

    const exec = (command: string, commandValue?: string) => {
        editorRef.current?.focus();
        // eslint-disable-next-line deprecation/deprecation
        document.execCommand(command, false, commandValue);
        const html = normalizeHtml(editorRef.current?.innerHTML ?? '');
        lastHtmlRef.current = html;
        onChange(html);
    };

    const setBlock = (block: BlockType) => {
        if (block === 'p') {
            exec('formatBlock', 'p');
            return;
        }
        exec('formatBlock', block);
    };

    const addLink = () => {
        const url = window.prompt('Enter URL');
        if (!url) return;
        exec('createLink', url);
    };

    const onInput = () => {
        const html = normalizeHtml(editorRef.current?.innerHTML ?? '');
        lastHtmlRef.current = html;
        onChange(html);
    };

    return (
        <div className={cn('rounded-md border border-input bg-background', className)}>
            <div className="flex flex-wrap items-center gap-1 border-b border-border px-2 py-2">
                <button
                    type="button"
                    onClick={() => exec('bold')}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Bold"
                >
                    <Bold className="size-4" />
                </button>
                <button
                    type="button"
                    onClick={() => exec('italic')}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Italic"
                >
                    <Italic className="size-4" />
                </button>
                <button
                    type="button"
                    onClick={() => exec('underline')}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Underline"
                >
                    <Underline className="size-4" />
                </button>

                <div className="mx-1 h-5 w-px bg-border" />

                <button
                    type="button"
                    onClick={() => setBlock('h1')}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Heading 1"
                >
                    <Heading1 className="size-4" />
                </button>
                <button
                    type="button"
                    onClick={() => setBlock('h2')}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Heading 2"
                >
                    <Heading2 className="size-4" />
                </button>
                <button
                    type="button"
                    onClick={() => setBlock('p')}
                    className="rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Paragraph"
                >
                    P
                </button>

                <div className="mx-1 h-5 w-px bg-border" />

                <button
                    type="button"
                    onClick={() => exec('insertUnorderedList')}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Bulleted list"
                >
                    <List className="size-4" />
                </button>
                <button
                    type="button"
                    onClick={() => exec('insertOrderedList')}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Numbered list"
                >
                    <ListOrdered className="size-4" />
                </button>

                <div className="mx-1 h-5 w-px bg-border" />

                <button
                    type="button"
                    onClick={addLink}
                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-foreground/80 hover:bg-muted"
                    aria-label="Insert link"
                >
                    <Link2 className="size-4" />
                </button>
            </div>

            <div className="relative">
                {safeValue === '' && (
                    <div className="pointer-events-none absolute left-3 top-3 text-sm text-muted-foreground">
                        {placeholder}
                    </div>
                )}
                <div
                    id={editorId}
                    ref={editorRef}
                    contentEditable
                    suppressContentEditableWarning
                    onInput={onInput}
                    className={cn(
                        'min-h-[260px] px-3 py-3 text-sm outline-none',
                        'prose max-w-none prose-headings:mt-6 prose-headings:mb-3 prose-p:mb-3 prose-ul:mb-3 prose-ol:mb-3 dark:prose-invert',
                    )}
                />
            </div>
        </div>
    );
}

