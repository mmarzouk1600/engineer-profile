<?php

namespace Modules\Album\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Album\Entities\AlbumFile;

/** @mixin AlbumFile */
class AlbumFileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'sort_order' => $this->sort_order,
            'extension' => pathinfo($this->original_name, PATHINFO_EXTENSION),
        ];
    }
}
