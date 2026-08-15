<?php

namespace Modules\Album\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Album\Entities\Purchase;

/** @mixin Purchase */
class PurchaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status?->value ?? $this->status,
            'payment_gateway' => $this->payment_gateway,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at,
            'album' => new AlbumResource($this->whenLoaded('album')),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
