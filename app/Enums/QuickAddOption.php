<?php

namespace App\Enums;

use App\Models\Product;
use Illuminate\Support\Collection;

enum QuickAddOption: string
{
    case Starter = 'starter';
    case Restock = 'restock';
    case Paints = 'paints';

    /** Category slug of optional extras, which are never part of a preset. */
    public const ACCESSORY_CATEGORY = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Starter => "I'm completely new to modelling",
            self::Restock => 'I need to restock on my essentials',
            self::Paints => 'With the paints, please!',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Starter => 'The kit plus every linked tool, material and paint to get you started.',
            self::Restock => 'The kit plus the essential materials (cement, putty and the like).',
            self::Paints => 'The kit plus the paints that go with this model.',
        };
    }

    public function bundleLabel(): string
    {
        return match ($this) {
            self::Starter => 'Starter Pack',
            self::Paints => 'Kit + Paints',
            self::Restock => 'Restock Pack',
        };
    }

    /**
     * Picks the linked add-ons that belong to this option, by category.
     *
     * @param  Collection<int, Product>  $recommendations
     * @return Collection<int, Product>
     */
    public function addOnsFrom(Collection $recommendations): Collection
    {
        return match ($this) {
            self::Starter => $recommendations->reject(fn (Product $p) => $p->category?->slug === self::ACCESSORY_CATEGORY)->values(),
            self::Restock => $recommendations->filter(fn (Product $p) => $p->category?->slug === 'materials')->values(),
            self::Paints => $recommendations->filter(fn (Product $p) => $p->category?->slug === 'paints')->values(),
        };
    }

    /**
     * Optional accessories linked to the kit; picked one by one, never preset.
     *
     * @param  Collection<int, Product>  $recommendations
     * @return Collection<int, Product>
     */
    public static function accessoriesFrom(Collection $recommendations): Collection
    {
        return $recommendations->filter(fn (Product $p) => $p->category?->slug === self::ACCESSORY_CATEGORY)->values();
    }
}
