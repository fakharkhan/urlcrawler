<?php

use App\Http\Controllers\ScrapedDocumentController;
use App\Models\ScrapedDocument;
use App\Models\Url;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $stats = [
        'urls_count' => Url::count(),
        'documents_count' => ScrapedDocument::count(),
    ];

    $recentDocuments = ScrapedDocument::query()
        ->with('url')
        ->latest()
        ->limit(8)
        ->get()
        ->map(fn (ScrapedDocument $doc) => [
            'id' => $doc->id,
            'url' => $doc->url->url,
            'title' => $doc->title,
            'created_at' => $doc->created_at->toISOString(),
        ]);

    return Inertia::render('dashboard', [
        'stats' => $stats,
        'recentDocuments' => $recentDocuments,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [ScrapedDocumentController::class, 'index'])->name('index');
    Route::post('/', [ScrapedDocumentController::class, 'store'])->name('store');
    Route::get('/{scraped_document}/download', [ScrapedDocumentController::class, 'download'])->name('download');
    Route::post('/{scraped_document}/rescrape', [ScrapedDocumentController::class, 'rescrape'])->name('rescrape');
    Route::get('/{scraped_document}', [ScrapedDocumentController::class, 'show'])->name('show');
});

require __DIR__.'/settings.php';
