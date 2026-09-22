<?php

use App\Models\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('cart:updated')]
    public function refreshCount(): void
    {
        $this->count = Cart::current()->totalQuantity();
    }
};
?>

<a href="{{ route('cart') }}"
   class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink/20 hover:border-brand hover:text-brand transition"
   aria-label="Cart">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
    </svg>
    @if ($count > 0)
        <span class="absolute -top-1 -right-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1.5 text-[11px] font-bold text-canvas">
            {{ $count }}
        </span>
    @endif
</a>
