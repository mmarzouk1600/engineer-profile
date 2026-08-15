<?php

namespace Modules\Album\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Album\Enums\AlbumStatus;

class Album extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'status',
        'cover_image_id',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => AlbumStatus::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AlbumImage::class)->orderBy('sort_order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AlbumFile::class)->orderBy('sort_order');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(AlbumImage::class, 'cover_image_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', AlbumStatus::Published);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
