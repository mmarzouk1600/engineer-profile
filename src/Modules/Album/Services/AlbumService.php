<?php

namespace Modules\Album\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Album\Entities\Album;
use Modules\Album\Enums\AlbumStatus;

class AlbumService
{
    public function listPublic(array $filters = []): LengthAwarePaginator
    {
        $query = Album::query()
            ->published()
            ->with(['coverImage'])
            ->withCount(['images', 'files'])
            ->latest();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(config('album.per_page', 12));
    }

    public function listAdmin(array $filters = []): LengthAwarePaginator
    {
        $query = Album::query()
            ->with(['coverImage', 'creator'])
            ->withCount(['images', 'files'])
            ->latest();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(config('album.per_page', 12));
    }

    public function create(array $data, int $userId): Album
    {
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['created_by'] = $userId;
        $data['currency'] = $data['currency'] ?? config('album.default_currency', 'SAR');
        $data['status'] = $data['status'] ?? AlbumStatus::Draft->value;

        return Album::create($data);
    }

    public function update(Album $album, array $data): Album
    {
        if (isset($data['title']) && $data['title'] !== $album->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $album->id);
        }

        $album->update($data);

        return $album->fresh(['coverImage', 'images', 'files']);
    }

    public function delete(Album $album): void
    {
        $album->delete();
    }

    public function setCoverImage(Album $album, int $imageId): Album
    {
        $image = $album->images()->whereKey($imageId)->firstOrFail();
        $album->update(['cover_image_id' => $image->id]);

        return $album->fresh(['coverImage']);
    }

    public function getBySlug(string $slug, bool $publishedOnly = true): ?Album
    {
        $query = Album::query()
            ->with(['coverImage', 'images', 'files'])
            ->withCount(['images', 'files'])
            ->where('slug', $slug);

        if ($publishedOnly) {
            $query->published();
        }

        return $query->first();
    }

    public function getDashboardStats(): array
    {
        return [
            'total_albums' => Album::count(),
            'published_albums' => Album::where('status', AlbumStatus::Published)->count(),
            'total_customers' => \App\Models\User::where('role', \App\Models\User::ROLE_CUSTOMER)->count(),
            'total_purchases' => \Modules\Album\Entities\Purchase::count(),
            'successful_payments' => \Modules\Album\Entities\Payment::where('status', \Modules\Album\Enums\PurchaseStatus::Paid)->count(),
            'pending_payments' => \Modules\Album\Entities\Payment::where('status', \Modules\Album\Enums\PurchaseStatus::Pending)->count(),
            'total_revenue' => (float) \Modules\Album\Entities\Payment::where('status', \Modules\Album\Enums\PurchaseStatus::Paid)->sum('amount'),
        ];
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (Album::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
