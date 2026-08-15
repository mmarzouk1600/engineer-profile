<?php

namespace Modules\Album\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AlbumImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'album_id',
        'path',
        'thumbnail_path',
        'original_name',
        'mime_type',
        'size',
        'sort_order',
    ];

    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk(config('album.image_disk', 'public'))->url($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail_path) {
            return $this->url;
        }

        return Storage::disk(config('album.image_disk', 'public'))->url($this->thumbnail_path);
    }
}
