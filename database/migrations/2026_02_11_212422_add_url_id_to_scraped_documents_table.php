<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scraped_documents', function (Blueprint $table) {
            $table->foreignId('url_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $urlCache = [];

        foreach (DB::table('scraped_documents')->get() as $document) {
            $url = $document->url;
            if (! isset($urlCache[$url])) {
                $existing = DB::table('urls')->where('url', $url)->first();
                $urlCache[$url] = $existing
                    ? $existing->id
                    : DB::table('urls')->insertGetId([
                        'url' => $url,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            DB::table('scraped_documents')
                ->where('id', $document->id)
                ->update(['url_id' => $urlCache[$url]]);
        }

        Schema::table('scraped_documents', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->foreignId('url_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraped_documents', function (Blueprint $table) {
            $table->string('url', 2048)->nullable()->after('url_id');
        });

        foreach (DB::table('scraped_documents')->get() as $document) {
            $url = DB::table('urls')->where('id', $document->url_id)->value('url');
            DB::table('scraped_documents')
                ->where('id', $document->id)
                ->update(['url' => $url]);
        }

        Schema::table('scraped_documents', function (Blueprint $table) {
            $table->dropForeign(['url_id']);
            $table->dropColumn('url_id');
        });
    }
};
