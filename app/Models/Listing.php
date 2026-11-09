<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'title', 'description', 'price', 'category', 'condition',
    'brand', 'size', 'location', 'delivery_mode', 'delivery_note',
    'photos', 'status',
])]
class Listing extends Model
{
    public const CATEGORIES = ['fashion', 'shoes', 'bags', 'electronics', 'home'];

    public const CONDITIONS = ['New', 'Like New', 'Good', 'Fair'];

    public const DELIVERY_MODES = ['pickup', 'delivery', 'both'];

    /**
     * Get the seller of the listing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to active listings only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Apply marketplace filters: search, category, condition, brand,
     * size, location, price range and sort.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['q'])) {
            $q = '%'.strtolower($filters['q']).'%';
            $query->where(function (Builder $w) use ($q) {
                $w->whereRaw('LOWER(title) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(brand) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(size) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(location) LIKE ?', [$q]);
            });
        }

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['condition']) && $filters['condition'] !== 'any') {
            $query->where('condition', $filters['condition']);
        }

        if (! empty($filters['brand']) && $filters['brand'] !== 'any') {
            $query->where('brand', $filters['brand']);
        }

        if (! empty($filters['size']) && $filters['size'] !== 'any') {
            $query->where('size', $filters['size']);
        }

        if (! empty($filters['location']) && $filters['location'] !== 'any') {
            $query->where('location', $filters['location']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', (int) $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', (int) $filters['max_price']);
        }

        return match ($filters['sort'] ?? 'newest') {
            'price-asc' => $query->orderBy('price'),
            'price-desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };
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
            'photos' => 'array',
        ];
    }
}
