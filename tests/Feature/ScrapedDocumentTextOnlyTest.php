<?php

use App\Models\ScrapedDocument;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('text-only scrape strips image markdown', function () {
    Http::fake([
        '*' => Http::response([
            'success' => true,
            'data' => [
                'markdown' => "# Page\n\n![Alt text](https://example.com/image.png)\n\nSome text.",
                'metadata' => ['title' => 'With Image'],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('documents.store'), [
        'url' => 'https://example.com',
        'include_images' => false,
    ]);

    $response->assertRedirect();
    $document = ScrapedDocument::first();
    expect($document->markdown)->not->toContain('![');
    expect($document->markdown)->toContain('Alt text');
});
