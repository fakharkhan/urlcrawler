<?php

use App\Models\ScrapedDocument;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        'api.firecrawl.dev/*' => Http::response([
            'success' => true,
            'data' => [
                'markdown' => '# Test Page\n\nThis is scraped content.',
                'metadata' => [
                    'title' => 'Test Page Title',
                ],
            ],
        ], 200),
    ]);
});

test('guests are redirected to login when accessing documents index', function () {
    $response = $this->get(route('documents.index'));
    $response->assertRedirect(route('login'));
});

test('guests are redirected to login when accessing document show', function () {
    $document = ScrapedDocument::factory()->create();
    $response = $this->get(route('documents.show', $document));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit documents index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('documents.index'));
    $response->assertOk();
});

test('authenticated users can scrape and save a url', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('documents.store'), [
        'url' => 'https://example.com',
    ]);

    $response->assertRedirect();
    expect(ScrapedDocument::count())->toBe(1);

    $document = ScrapedDocument::with('url')->first();
    expect($document->url->url)->toBe('https://example.com');
    expect($document->title)->toBe('Test Page Title');
    expect($document->markdown)->toContain('# Test Page');
});

test('store validates url is required', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('documents.store'), [
        'url' => '',
    ]);

    $response->assertSessionHasErrors('url');
});

test('store validates url format', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('documents.store'), [
        'url' => 'not-a-valid-url',
    ]);

    $response->assertSessionHasErrors('url');
});

test('authenticated users can view a saved document', function () {
    $user = User::factory()->create();
    $document = ScrapedDocument::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('documents.show', $document));
    $response->assertOk();
});

test('guests are redirected to login when downloading document', function () {
    $document = ScrapedDocument::factory()->create();
    $response = $this->get(route('documents.download', $document));
    $response->assertRedirect(route('login'));
});

test('authenticated users can download document markdown', function () {
    $user = User::factory()->create();
    $document = ScrapedDocument::factory()->create([
        'markdown' => '# Hello World',
        'title' => 'Test Document',
    ]);
    $this->actingAs($user);

    $response = $this->get(route('documents.download', $document));

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'attachment; filename="test-document.md"');
    expect($response->getContent())->toBe('# Hello World');
});

test('same url creates one url record and multiple documents', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('documents.store'), ['url' => 'https://example.com']);
    $this->post(route('documents.store'), ['url' => 'https://example.com']);

    expect(\App\Models\Url::count())->toBe(1);
    expect(ScrapedDocument::count())->toBe(2);
});

test('guests are redirected to login when re-scraping', function () {
    $document = ScrapedDocument::factory()->create();
    $response = $this->post(route('documents.rescrape', $document));
    $response->assertRedirect(route('login'));
});

test('authenticated users can re-scrape a document', function () {
    $user = User::factory()->create();
    $document = ScrapedDocument::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('documents.rescrape', $document));

    $response->assertRedirect();
    expect(ScrapedDocument::count())->toBe(2);
    $newDocument = ScrapedDocument::latest()->first();
    expect($newDocument->url_id)->toBe($document->url_id);
});

