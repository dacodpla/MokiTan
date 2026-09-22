<x-layouts.store>
    <x-slot name="title">{{ $product->name }} — Moki-Tan</x-slot>

    <div class="container mx-auto py-10">
        <a href="{{ url()->previous() }}" class="type-body text-brand hover:underline inline-flex items-center gap-1 mb-6">
            ← Back
        </a>

        <div class="grid gap-10 lg:grid-cols-2">
            <div class="rounded-xl bg-surface aspect-square overflow-hidden">
                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}"
                     class="h-full w-full object-cover">
            </div>

            <div class="flex flex-col">
                <div class="type-body font-bold uppercase text-ink/60">{{ $product->brand }}</div>
                <h1 class="type-h1 mt-1">{{ $product->name }}</h1>
                <div class="type-h4 mt-3">{{ \App\Support\Money::idr($product->price) }}</div>

                <p class="type-body mt-6 text-ink-alt/80">{{ $product->description }}</p>

                @if (! empty($product->specifications))
                    <dl class="mt-6 grid grid-cols-2 gap-2 text-sm">
                        @foreach ($product->specifications as $key => $value)
                            <div class="rounded-lg bg-surface/40 p-3">
                                <dt class="text-ink/60 uppercase">{{ str_replace('_', ' ', $key) }}</dt>
                                <dd class="font-bold">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($product->mold_history)
                    <div class="mt-6 rounded-xl border border-ink/10 p-4">
                        <div class="type-body font-bold mb-1">Mold history</div>
                        <p class="type-body text-ink-alt/70">{{ $product->mold_history }}</p>
                    </div>
                @endif

                <div class="mt-8 space-y-4">
                    <livewire:product.add-to-cart :product="$product" />
                    @if ($product->recommendations->isNotEmpty())
                        <livewire:product.quick-add :product="$product" />
                    @endif
                </div>

                @if ($product->recommendations->isNotEmpty())
                    <div class="mt-10">
                        <div class="type-h5 mb-3">Recommended tools & materials</div>
                        <ul class="grid gap-2">
                            @foreach ($product->recommendations as $rec)
                                <li class="flex items-center justify-between rounded-lg border border-ink/10 p-3">
                                    <a href="{{ route('products.show', $rec->slug) }}" class="type-body hover:text-brand">
                                        {{ $rec->name }} <span class="text-ink/50">— {{ $rec->brand }}</span>
                                    </a>
                                    <span class="type-body font-bold">{{ \App\Support\Money::idr($rec->price) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.store>
