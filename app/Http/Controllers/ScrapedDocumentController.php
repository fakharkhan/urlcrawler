<?php

namespace App\Http\Controllers;

use App\Http\Requests\RescrapeDocumentRequest;
use App\Http\Requests\StoreScrapedDocumentRequest;
use App\Models\ScrapedDocument;
use App\Models\Url;
use App\Services\FirecrawlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ScrapedDocumentController extends Controller
{
    public function __construct(
        protected FirecrawlService $firecrawl
    ) {}

    public function index(Request $request): Response
    {
        $documents = ScrapedDocument::query()
            ->with('url')
            ->latest()
            ->get()
            ->map(fn (ScrapedDocument $doc) => [
                'id' => $doc->id,
                'url' => $doc->url->url,
                'title' => $doc->title,
                'created_at' => $doc->created_at->toISOString(),
            ]);

        return Inertia::render('documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function store(StoreScrapedDocumentRequest $request): RedirectResponse
    {
        $urlString = $request->validated('url');
        $includeImages = $request->boolean('include_images');

        $scraped = $this->firecrawl->scrape($urlString, $includeImages);

        if ($scraped === null) {
            return back()->withErrors([
                'url' => 'Unable to scrape this URL. Please check the URL and try again.',
            ]);
        }

        $url = Url::firstOrCreate(['url' => $urlString]);

        $document = ScrapedDocument::create([
            'url_id' => $url->id,
            'title' => $scraped['title'] ?? parse_url($urlString, PHP_URL_HOST) ?? 'Untitled',
            'markdown' => $scraped['markdown'],
        ]);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Page scraped and saved successfully.');
    }

    public function show(ScrapedDocument $scraped_document): Response
    {
        $scraped_document->load('url');

        return Inertia::render('documents/Show', [
            'document' => [
                'id' => $scraped_document->id,
                'url' => $scraped_document->url->url,
                'title' => $scraped_document->title,
                'markdown' => $scraped_document->markdown,
                'created_at' => $scraped_document->created_at->toISOString(),
            ],
        ]);
    }

    public function download(ScrapedDocument $scraped_document): HttpResponse
    {
        $filename = Str::slug($scraped_document->title ?? 'document').'.md';

        return response($scraped_document->markdown, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function rescrape(ScrapedDocument $scraped_document, RescrapeDocumentRequest $request): RedirectResponse
    {
        $scraped_document->load('url');
        $includeImages = $request->boolean('include_images');

        $scraped = $this->firecrawl->scrape($scraped_document->url->url, $includeImages);

        if ($scraped === null) {
            return back()->withErrors([
                'rescrape' => 'Unable to re-scrape this URL. Please try again later.',
            ]);
        }

        $newDocument = ScrapedDocument::create([
            'url_id' => $scraped_document->url_id,
            'title' => $scraped['title'] ?? $scraped_document->title ?? 'Untitled',
            'markdown' => $scraped['markdown'],
        ]);

        return redirect()
            ->route('documents.show', $newDocument)
            ->with('success', 'URL re-scraped successfully.');
    }
}
