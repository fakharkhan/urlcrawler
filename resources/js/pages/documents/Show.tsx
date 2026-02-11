import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Download, RefreshCw } from 'lucide-react';
import { useState } from 'react';
import ReactMarkdown from 'react-markdown';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface ScrapedDocument {
    id: number;
    url: string;
    title: string | null;
    markdown: string;
    created_at: string;
}

interface Props {
    document: ScrapedDocument;
}

export default function DocumentShow({ document }: Props) {
    const [rescraping, setRescraping] = useState(false);

    function handleRescrape() {
        setRescraping(true);
        router.post(`/documents/${document.id}/rescrape`, {
            include_images: false,
        }, {
            preserveScroll: true,
            onFinish: () => setRescraping(false),
        });
    }
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Documents', href: '/documents' },
        { title: document.title || document.url, href: `/documents/${document.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={document.title || 'Document'} />

            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/documents">
                            <ArrowLeft className="mr-2 size-4" />
                            Back to documents
                        </Link>
                    </Button>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleRescrape}
                            disabled={rescraping}
                        >
                            <RefreshCw className={`mr-2 size-4 ${rescraping ? 'animate-spin' : ''}`} />
                            Re-scrape
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <a href={`/documents/${document.id}/download`} download>
                                <Download className="mr-2 size-4" />
                                Download Markdown
                            </a>
                        </Button>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="truncate">
                            {document.title || 'Untitled'}
                        </CardTitle>
                        <a
                            href={document.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-sm text-muted-foreground hover:underline"
                        >
                            {document.url}
                        </a>
                    </CardHeader>
                    <CardContent>
                        <article className="prose prose-neutral dark:prose-invert max-w-none overflow-x-auto rounded-lg border bg-muted/30 p-4">
                            <ReactMarkdown
                                components={{
                                    a: ({ href, children }) => (
                                        <a
                                            href={href}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-primary underline"
                                        >
                                            {children}
                                        </a>
                                    ),
                                }}
                            >
                                {document.markdown}
                            </ReactMarkdown>
                        </article>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
