@php
    $orders = auth()->user()->orders()->latest()->get();
@endphp

<x-layouts.store>
    <x-slot name="title">Your Account — Moki-Tan</x-slot>

    <div class="container mx-auto py-10">
        <x-store.section-heading title="Your Account" />

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_1fr]">
            <section class="rounded-xl border border-ink/10 p-6">
                <div class="type-h4 mb-4">Profile</div>
                @include('profile.partials.update-profile-information-form')
            </section>

            <section class="rounded-xl border border-ink/10 p-6">
                <div class="type-h4 mb-4">Password</div>
                @include('profile.partials.update-password-form')
            </section>
        </div>

        <section class="mt-8 rounded-xl border border-ink/10 p-6">
            <div class="type-h4 mb-4">Order history</div>
            @if ($orders->isEmpty())
                <p class="type-body text-ink/60">No orders yet. <a href="{{ route('catalog') }}" class="text-brand hover:underline">Start shopping →</a></p>
            @else
                <ul class="divide-y divide-ink/10">
                    @foreach ($orders as $order)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="type-body font-bold">{{ $order->order_number }}</div>
                                <div class="type-body text-ink/60 text-sm">
                                    {{ $order->created_at->format('d M Y') }}
                                    · <span class="uppercase">{{ $order->status->label() }}</span>
                                    · {{ \App\Support\Money::idr($order->total) }}
                                </div>
                            </div>
                            <a href="{{ route('orders.track', $order) }}" class="type-body text-brand hover:underline">Track →</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="mt-8 rounded-xl border border-brand/30 p-6">
            <div class="type-h4 mb-4 text-brand">Danger zone</div>
            @include('profile.partials.delete-user-form')
        </section>
    </div>
</x-layouts.store>
