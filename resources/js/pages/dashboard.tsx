import { Head, Link } from '@inertiajs/react';
import { FileText, Globe, LayoutGrid } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

interface DocumentSummary {
    id: number;
    url: string;
    title: string | null;
    created_at: string;
}

interface Props {
    stats: {
        urls_count: number;
        documents_count: number;
    };
    recentDocuments: DocumentSummary[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard({ stats, recentDocuments }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                URLs Scraped
                            </CardTitle>
                            <Globe className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.urls_count}</div>
                            <p className="text-muted-foreground text-xs">
                                Unique URLs in your collection
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Documents Saved
                            </CardTitle>
                            <FileText className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.documents_count}</div>
                            <p className="text-muted-foreground text-xs">
                                Scraped pages stored as markdown
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0">
                        <div>
                            <CardTitle>Recent Documents</CardTitle>
                            <CardDescription>
                                Your latest scraped pages
                            </CardDescription>
                        </div>
                        <Button asChild size="sm">
                            <Link href="/documents">View All</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {recentDocuments.length === 0 ? (
                            <div className="flex flex-col items-center justify-center gap-4 py-12">
                                <LayoutGrid className="size-12 text-muted-foreground" />
                                <div className="text-center">
                                    <p className="font-medium">No documents yet</p>
                                    <p className="text-muted-foreground text-sm">
                                        Scrape a URL to get started
                                    </p>
                                </div>
                                <Button asChild>
                                    <Link href="/documents">Go to Documents</Link>
                                </Button>
                            </div>
                        ) : (
                            <ul className="grid gap-2">
                                {recentDocuments.map((doc) => (
                                    <li key={doc.id}>
                                        <Link
                                            href={`/documents/${doc.id}`}
                                            className="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent/50"
                                        >
                                            <FileText className="size-5 shrink-0 text-muted-foreground" />
                                            <div className="min-w-0 flex-1">
                                                <p className="truncate font-medium">
                                                    {doc.title || doc.url}
                                                </p>
                                                <p className="truncate text-sm text-muted-foreground">
                                                    {doc.url}
                                                </p>
                                            </div>
                                            <span className="text-muted-foreground shrink-0 text-xs">
                                                {new Date(doc.created_at).toLocaleDateString()}
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
