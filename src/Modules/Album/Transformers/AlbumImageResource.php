<?php

namespace Modules\Album\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Album\Entities\AlbumImage;

/** @mixin AlbumImage */
class AlbumImageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'sort_order' => $this->sort_order,
        ];
    }
}
