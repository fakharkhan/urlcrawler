import { Head, Link, router, useForm } from '@inertiajs/react';
import { Download, FileText, Globe, ImageIcon, Loader2, RefreshCw } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

interface ScrapedDocumentSummary {
    id: number;
    url: string;
    title: string | null;
    created_at: string;
}

interface Props {
    documents: ScrapedDocumentSummary[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documents',
        href: '/documents',
    },
];

export default function DocumentsIndex({ documents }: Props) {
    const [rescrapingId, setRescrapingId] = useState<number | null>(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        url: '',
        include_images: false,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/documents', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="URL Crawler - Scrape & Save Documents" />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    variant="default"
                    title="URL Crawler"
                    description="Enter a URL to scrape and save the page content as markdown"
                />

                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Globe className="size-5" />
                            Scrape a URL
                        </CardTitle>
                        <CardDescription>
                            Firecrawl will fetch the page and extract the content as markdown
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="flex gap-2">
                                <div className="min-w-0 flex-1">
                                    <Label htmlFor="url" className="sr-only">
                                        URL
                                    </Label>
                                    <Input
                                        id="url"
                                        type="url"
                                        placeholder="https://example.com"
                                        value={data.url}
                                        onChange={(e) => setData('url', e.target.value)}
                                        className="h-10"
                                        disabled={processing}
                                        autoFocus
                                        required
                                    />
                                    <InputError message={errors.url} className="mt-1" />
                                </div>
                                <Button type="submit" disabled={processing} size="lg" className="shrink-0">
                                    {processing ? (
                                        <Loader2 className="size-4 animate-spin" />
                                    ) : (
                                        'Scrape & Save'
                                    )}
                                </Button>
                            </div>
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="include_images"
                                    checked={data.include_images}
                                    onCheckedChange={(checked) =>
                                        setData('include_images', checked === true)
                                    }
                                    disabled={processing}
                                />
                                <Label
                                    htmlFor="include_images"
                                    className="flex cursor-pointer items-center gap-2 text-sm font-normal"
                                >
                                    <ImageIcon className="size-4 text-muted-foreground" />
                                    Include images (uncheck for text-only)
                                </Label>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <div>
                    <h2 className="mb-3 text-lg font-semibold">Saved Documents</h2>
                    {documents.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center gap-2 py-12">
                                <FileText className="size-12 text-muted-foreground" />
                                <p className="text-muted-foreground">
                                    No documents yet. Scrape a URL to get started.
                                </p>
                            </CardContent>
                        </Card>
                    ) : (
                        <ul className="grid gap-2">
                            {documents.map((doc) => (
                                <li
                                    key={doc.id}
                                    className="flex items-center gap-3 rounded-lg border p-4 transition-colors hover:bg-accent/50"
                                >
                                    <FileText className="size-5 shrink-0 text-muted-foreground" />
                                    <Link
                                        href={`/documents/${doc.id}`}
                                        className="min-w-0 flex-1"
                                    >
                                        <p className="truncate font-medium">
                                            {doc.title || doc.url}
                                        </p>
                                        <p className="truncate text-sm text-muted-foreground">
                                            {doc.url}
                                        </p>
                                    </Link>
                                    <span className="text-muted-foreground shrink-0 text-sm">
                                        {new Date(doc.created_at).toLocaleDateString()}
                                    </span>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="shrink-0"
                                        onClick={(e) => {
                                            e.preventDefault();
                                            setRescrapingId(doc.id);
                                            router.post(
                                                `/documents/${doc.id}/rescrape`,
                                                { include_images: false },
                                                {
                                                    preserveScroll: true,
                                                    onFinish: () => setRescrapingId(null),
                                                },
                                            );
                                        }}
                                        disabled={rescrapingId === doc.id}
                                        title="Re-scrape URL"
                                    >
                                        <RefreshCw
                                            className={`size-4 ${
                                                rescrapingId === doc.id ? 'animate-spin' : ''
                                            }`}
                                        />
                                    </Button>
                                    <Button variant="ghost" size="icon" className="shrink-0" asChild>
                                        <a
                                            href={`/documents/${doc.id}/download`}
                                            download
                                            title="Download Markdown"
                                        >
                                            <Download className="size-4" />
                                        </a>
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
