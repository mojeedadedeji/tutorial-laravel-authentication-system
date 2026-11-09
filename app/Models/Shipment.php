<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'vehicle_id', 'reference', 'origin', 'destination', 'status', 'weight_kg', 'amount', 'shipped_at', 'delivered_at'])]
class Shipment extends Model
{
    /** @use HasFactory<\Database\Factories\ShipmentFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'in_transit', 'delivered', 'delayed', 'cancelled'];

    /**
     * Get the user that owns the shipment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle assigned to this shipment.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the payments for this shipment.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the status history for this shipment.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ShipmentStatusHistory::class)->orderBy('created_at');
    }

    /**
     * Record status history automatically on create and status change.
     */
    protected static function booted(): void
    {
        static::created(function (Shipment $shipment) {
            $shipment->statusHistories()->create(['status' => $shipment->status]);
        });

        static::updated(function (Shipment $shipment) {
            if ($shipment->wasChanged('status')) {
                $shipment->statusHistories()->create(['status' => $shipment->status]);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'amount' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
