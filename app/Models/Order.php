<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'buyer_id', 'listing_id', 'seller_id', 'title', 'price',
    'delivery_cost', 'fee', 'total', 'address', 'payment_method',
    'payment_status', 'paystack_reference', 'paid_at',
    'status', 'status_history',
])]
class Order extends Model
{
    public const STATUSES = ['awaiting_dispatch', 'dispatched', 'delivered', 'cancelled', 'refunded'];

    /** Allowed status transitions (mirrors the prototype rules). */
    public const TRANSITIONS = [
        'awaiting_dispatch' => ['dispatched', 'cancelled'],
        'dispatched' => ['delivered', 'cancelled'],
        'delivered' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    /** Prototype fee assumption — clearly labelled, not a real charge. */
    public const PROTO_FEE = 500;

    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'pay_on_delivery', 'simulated', 'refunded'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function recordTransition(string $status, ?string $note = null): void
    {
        $history = $this->status_history ?? [];
        $history[] = [
            'status' => $status,
            'time' => now()->toIso8601String(),
            'note' => $note ?? 'Status updated',
        ];
        $this->status_history = $history;
        $this->status = $status;
        $this->save();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'delivery_cost' => 'integer',
            'fee' => 'integer',
            'total' => 'integer',
            'status_history' => 'array',
            'paid_at' => 'datetime',
        ];
    }
}
