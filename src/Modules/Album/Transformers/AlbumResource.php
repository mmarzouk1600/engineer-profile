<?php

namespace Modules\Album\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Album\Entities\Album;

/** @mixin Album */
class AlbumResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status?->value ?? $this->status,
            'cover_image' => $this->whenLoaded('coverImage', fn () => new AlbumImageResource($this->coverImage)),
            'images_count' => $this->whenCounted('images'),
            'files_count' => $this->whenCounted('files'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
