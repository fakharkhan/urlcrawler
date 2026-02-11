<?php

namespace Database\Factories;

use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScrapedDocument>
 */
class ScrapedDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url_id' => Url::factory(),
            'title' => fake()->sentence(),
            'markdown' => '# '.fake()->sentence()."\n\n".fake()->paragraphs(3, true),
        ];
    }
}
