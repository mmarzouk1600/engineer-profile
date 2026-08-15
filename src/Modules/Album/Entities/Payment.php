<?php

namespace Modules\Album\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Album\Enums\PurchaseStatus;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'album_id',
        'purchase_id',
        'gateway',
        'charge_id',
        'status',
        'amount',
        'currency',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => PurchaseStatus::class,
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function isPaid(): bool
    {
        return $this->status === PurchaseStatus::Paid;
    }
}
