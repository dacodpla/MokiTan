<?php

use App\Models\CartItem;
use Livewire\Component;

new class extends Component
{
    public CartItem $item;

    public bool $isGift = false;

    public string $giftNote = '';

    public function mount(CartItem $item): void
    {
        $this->item = $item;
        $this->isGift = (bool) $item->is_gift;
        $this->giftNote = (string) ($item->gift_note ?? '');
    }

    public function updated(): void
    {
        $this->item->update([
            'is_gift' => $this->isGift,
            'gift_note' => $this->isGift ? $this->giftNote : null,
        ]);
    }
};
?>

<div class="rounded-lg border border-ink/10 p-3">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" wire:model.live="isGift"
               class="rounded border-ink/40 text-brand focus:ring-brand">
        <span class="type-body font-medium">Add gift wrap + note</span>
    </label>
    @if ($isGift)
        <textarea wire:model.live.debounce.500ms="giftNote"
                  rows="2" maxlength="200"
                  placeholder="Write a short gift message..."
                  class="mt-3 w-full rounded-lg border border-ink/20 p-2 type-body focus:border-brand focus:ring-brand"></textarea>
    @endif
</div>
