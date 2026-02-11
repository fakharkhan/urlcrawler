<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_id',
        'title',
        'markdown',
    ];

    /**
     * @return BelongsTo<Url, $this>
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }
}
