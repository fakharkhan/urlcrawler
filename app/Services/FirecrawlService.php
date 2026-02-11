<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FirecrawlService
{
    public function __construct(
        protected ?string $apiKey = null,
        protected string $baseUrl = 'https://api.firecrawl.dev/v1'
    ) {
        $this->apiKey ??= config('services.firecrawl.api_key');
        $this->baseUrl = config('services.firecrawl.base_url', $baseUrl);
    }

    /**
     * Scrape a URL and return page content as markdown.
     *
     * @param  bool  $includeImages  When false, strips image markdown for text-only output
     * @return array{markdown: string, title?: string}|null
     */
    public function scrape(string $url, bool $includeImages = false): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/scrape", [
            'url' => $url,
            'formats' => ['markdown'],
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('data');

        if (! is_array($data) || empty($data['markdown'])) {
            return null;
        }

        $markdown = $data['markdown'];

        if (! $includeImages) {
            $markdown = $this->stripImageMarkdown($markdown);
        }

        return [
            'markdown' => $markdown,
            'title' => $data['metadata']['title'] ?? null,
        ];
    }

    /**
     * Remove image markdown syntax, keeping alt text as plain text.
     */
    protected function stripImageMarkdown(string $markdown): string
    {
        return preg_replace('/!\[([^\]]*)\]\([^)]+\)/', '$1', $markdown);
    }
}
