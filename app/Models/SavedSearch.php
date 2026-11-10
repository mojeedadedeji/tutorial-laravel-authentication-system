<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'filters', 'notify'])]
class SavedSearch extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'notify' => 'boolean',
        ];
    }

    /**
     * Does a listing match this saved search's filters?
     */
    public function matches(Listing $listing): bool
    {
        $f = $this->filters ?? [];

        if (! empty($f['query'])) {
            $q = mb_strtolower($f['query']);
            $hay = mb_strtolower(implode(' ', array_filter([
                $listing->title, $listing->description, $listing->brand, $listing->size, $listing->location,
            ])));
            if (! str_contains($hay, $q)) {
                return false;
            }
        }
        if (! empty($f['category']) && $f['category'] !== 'all' && $listing->category !== $f['category']) {
            return false;
        }
        if (isset($f['min_price']) && $listing->price < (int) $f['min_price']) {
            return false;
        }
        if (isset($f['max_price']) && $listing->price > (int) $f['max_price']) {
            return false;
        }
        if (! empty($f['condition']) && $listing->condition !== $f['condition']) {
            return false;
        }
        if (! empty($f['location']) && mb_strtolower($listing->location) !== mb_strtolower($f['location'])) {
            return false;
        }

        return true;
    }
}
