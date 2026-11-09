<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['shipment_id', 'status', 'note'])]
class ShipmentStatusHistory extends Model
{
    public const UPDATED_AT = null;

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
