<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'plate_number', 'driver_name', 'driver_phone', 'status'])]
class Vehicle extends Model
{
    public const TYPES = ['van', 'truck', 'tipper', 'bike', 'trailer'];

    public const STATUSES = ['available', 'on_trip', 'maintenance', 'retired'];

    /**
     * Get the shipments assigned to this vehicle.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }
}
