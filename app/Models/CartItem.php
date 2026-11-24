<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['cart_id', 'parent_id', 'product_id', 'quantity', 'is_gift', 'gift_note', 'bundle_meta'])]
class CartItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_gift' => 'boolean',
            'bundle_meta' => 'array',
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The bundle this line was quick-added with, if any.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Items quick-added together with this kit; they live inside its line.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isBundleChild(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * This line plus everything bundled inside it.
     */
    public function bundleTotal(): int
    {
        return $this->lineTotal() + (int) $this->children->sum(fn (self $child) => $child->lineTotal());
    }

    public function lineTotal(): int
    {
        return (int) $this->product->price * $this->quantity;
    }
}
