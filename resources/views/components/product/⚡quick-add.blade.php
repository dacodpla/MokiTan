<?php

use App\Enums\QuickAddOption;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public Product $product;

    public bool $open = false;

    public ?string $option = null;

    /** @var array<int, int> */
    public array $accessories = [];

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function select(string $value): void
    {
        $this->resetErrorBag();
        $this->option = QuickAddOption::tryFrom($value)?->value;
    }

    public function toggleAccessory(int $id): void
    {
        if (in_array($id, $this->accessories, true)) {
            $this->accessories = array_values(array_diff($this->accessories, [$id]));
        } else {
            $this->accessories[] = $id;
        }
    }

    /**
     * Optional accessories linked to this kit.
     *
     * @return Collection<int, Product>
     */
    protected function accessoryChoices(): Collection
    {
        return QuickAddOption::accessoriesFrom($this->product->recommendations()->with('category')->get());
    }

    /**
     * The linked add-ons that belong to the given option.
     *
     * @return Collection<int, Product>
     */
    protected function addOnsFor(QuickAddOption $option): Collection
    {
        return $option->addOnsFrom($this->product->recommendations()->with('category')->get());
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        $choice = $this->option ? QuickAddOption::tryFrom($this->option) : null;

        return [
            'options' => QuickAddOption::cases(),
            'counts' => collect(QuickAddOption::cases())
                ->mapWithKeys(fn (QuickAddOption $o) => [$o->value => $this->addOnsFor($o)->where('stock', '>', 0)->count()]),
            'choice' => $choice,
            'addOns' => $choice ? $this->addOnsFor($choice) : collect(),
            'accessoryChoices' => $this->accessoryChoices(),
        ];
    }

    public function addBundle(): void
    {
        if (! Auth::check()) {
            session()->put('url.intended', url()->previous());
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->resetErrorBag();

        $choice = $this->option ? QuickAddOption::tryFrom($this->option) : null;

        if (! $choice) {
            $this->addError('bundle', 'Pick one of the options first.');

            return;
        }

        $cart = Cart::current();
        $kit = $this->product->fresh();

        if ($kit->stock - $cart->quantityOf($kit->id) < 1) {
            $this->addError('bundle', "{$kit->name} has no stock left to add.");

            return;
        }

        $picked = $this->accessoryChoices()->whereIn('id', $this->accessories);

        $extras = $this->addOnsFor($choice)
            ->concat($picked)
            ->filter(fn (Product $extra) => $extra->stock - $cart->quantityOf($extra->id) >= 1)
            ->values();

        $bundle = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $kit->id,
            'quantity' => 1,
            'bundle_meta' => ['label' => $choice->bundleLabel()],
        ]);

        foreach ($extras as $extra) {
            CartItem::create([
                'cart_id' => $cart->id,
                'parent_id' => $bundle->id,
                'product_id' => $extra->id,
                'quantity' => 1,
            ]);
        }

        $this->reset('option', 'accessories');
        $this->open = false;
        $this->dispatch('cart:updated');
    }
};
?>

<div>
    @if ($product->stock < 1)
        <button type="button" disabled class="btn-outline w-full opacity-40 cursor-not-allowed">Stok Habis</button>
    @else
        <button wire:click="$set('open', true)" type="button" class="btn-outline w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Quick Add
        </button>
    @endif

    @if ($open)
        <div class="fixed inset-0 z-50 bg-ink/60 grid place-items-center p-4"
             wire:click.self="$set('open', false)">
            <div class="w-full max-w-lg max-h-[90vh] overflow-auto rounded-xl bg-canvas p-6 shadow-lg">
                <div class="flex items-center justify-between mb-1">
                    <div class="type-h4">Quick Add</div>
                    <button wire:click="$set('open', false)" class="text-ink/50 hover:text-brand text-2xl">×</button>
                </div>
                <p class="type-body text-ink/60 mb-5">Where are you at with {{ $product->name }}?</p>

                <div class="space-y-3 mb-5">
                    @foreach ($options as $o)
                        @php
                            $available = $counts[$o->value];
                            $disabled = $available === 0 && $o !== \App\Enums\QuickAddOption::Starter;
                            $active = $option === $o->value;
                        @endphp
                        <button type="button" wire:key="opt-{{ $o->value }}"
                                wire:click="select('{{ $o->value }}')"
                                @disabled($disabled)
                                class="w-full text-left shape-card border p-4 transition
                                       {{ $active ? 'border-brand bg-brand/5' : 'border-ink/15 hover:border-brand' }}
                                       {{ $disabled ? 'opacity-40 cursor-not-allowed hover:border-ink/15' : '' }}">
                            <span class="block type-body font-bold">{{ $o->label() }}</span>
                            <span class="block type-body text-ink/60">{{ $o->description() }}</span>
                            <span class="block mt-1 text-xs font-bold {{ $disabled ? 'text-ink/50' : 'text-brand' }}">
                                {{ $disabled ? 'Nothing linked to this kit yet' : $available.' linked '.\Illuminate\Support\Str::plural('item', $available).' in stock' }}
                            </span>
                        </button>
                    @endforeach
                </div>

                @if ($choice)
                    <div class="type-body font-bold mb-2">You'll get</div>
                    <ul class="space-y-2 mb-5 max-h-56 overflow-auto">
                        <li class="flex items-center justify-between gap-3 p-3 rounded-lg border border-ink/10">
                            <span class="type-body font-medium">{{ $product->name }}</span>
                            <span class="type-body font-bold">{{ \App\Support\Money::idr($product->price) }}</span>
                        </li>
                        @foreach ($addOns as $rec)
                            @php $soldOut = $rec->stock < 1; @endphp
                            <li wire:key="add-{{ $rec->id }}"
                                class="flex items-center justify-between gap-3 p-3 rounded-lg border border-ink/10 {{ $soldOut ? 'opacity-50' : '' }}">
                                <span>
                                    <span class="type-body font-medium">{{ $rec->name }}</span>
                                    <span class="block type-body text-ink/60 text-xs">
                                        {{ $rec->category?->name }} · {{ $soldOut ? 'Stok Habis — skipped' : 'Stok: '.$rec->stock }}
                                    </span>
                                </span>
                                <span class="type-body font-bold">{{ \App\Support\Money::idr($rec->price) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($accessoryChoices->isNotEmpty())
                    <div class="type-body font-bold mb-1">Optional accessories</div>
                    <p class="type-body text-ink/60 text-sm mb-2">Pick any you want on top of your choice above.</p>
                    <ul class="space-y-2 mb-5">
                        @foreach ($accessoryChoices as $acc)
                            @php $soldOut = $acc->stock < 1; @endphp
                            <li wire:key="acc-{{ $acc->id }}">
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-ink/10 {{ $soldOut ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-brand' }}">
                                    <input type="checkbox"
                                           wire:click="toggleAccessory({{ $acc->id }})"
                                           @checked(in_array($acc->id, $accessories, true))
                                           @disabled($soldOut)
                                           class="rounded border-ink/40 text-brand focus:ring-brand">
                                    <span class="flex-1">
                                        <span class="type-body font-medium">{{ $acc->name }}</span>
                                        <span class="block type-body text-ink/60 text-xs">
                                            {{ $acc->brand }} · {{ $soldOut ? 'Stok Habis' : 'Stok: '.$acc->stock }}
                                        </span>
                                    </span>
                                    <span class="type-body font-bold">{{ \App\Support\Money::idr($acc->price) }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @error('bundle')
                    <p class="mb-4 type-body text-sm text-brand" role="alert">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-between border-t border-ink/10 pt-4">
                    <div>
                        <div class="type-body text-ink/60">Total</div>
                        <div class="type-h4">
                            {{ \App\Support\Money::idr(
                                ($choice ? $product->price : 0)
                                + $addOns->where('stock', '>', 0)->sum('price')
                                + $accessoryChoices->whereIn('id', $accessories)->where('stock', '>', 0)->sum('price')
                            ) }}
                        </div>
                    </div>
                    <button wire:click="addBundle" @disabled(! $choice) class="btn-primary {{ $choice ? '' : 'opacity-40 cursor-not-allowed' }}">Add to cart</button>
                </div>
            </div>
        </div>
    @endif
</div>
