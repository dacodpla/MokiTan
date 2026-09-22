<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

#[Fillable(['user_id', 'session_id'])]
class Cart extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public static function current(): self
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $cart = $userId
            ? self::firstOrCreate(['user_id' => $userId])
            : self::firstOrCreate(['session_id' => $sessionId]);

        if ($userId && $cart->session_id) {
            $cart->update(['session_id' => null]);
        }

        return $cart;
    }

    public function totalQuantity(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function subtotal(): int
    {
        return (int) $this->items()
            ->with('product')
            ->get()
            ->sum(fn (CartItem $item) => $item->product->price * $item->quantity);
    }
}
