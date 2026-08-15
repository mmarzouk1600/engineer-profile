<?php

namespace Modules\Album\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Album\Entities\Album;

/** @mixin Album */
class AlbumDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = auth()->user();
        $purchased = $user ? app(\Modules\Album\Services\PurchaseService::class)->userHasPaidAccess($user, $this->resource) : false;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status?->value ?? $this->status,
            'cover_image' => $this->whenLoaded('coverImage', fn () => new AlbumImageResource($this->coverImage)),
            'images' => AlbumImageResource::collection($this->whenLoaded('images')),
            'files' => AlbumFileResource::collection($this->whenLoaded('files')),
            'images_count' => $this->whenCounted('images', $this->images_count ?? null),
            'files_count' => $this->whenCounted('files', $this->files_count ?? null),
            'purchased' => $purchased,
            'meta' => [
                'title' => $this->title,
                'description' => str($this->description)->limit(160)->value(),
                'og_image' => $this->coverImage?->url,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
