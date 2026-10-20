@props(['items'])

{{--
    $items: array<int, array{label: string, url?: string|null}>
    The last item (or any item without a url) renders as plain text.
--}}
<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex flex-wrap items-center gap-2 type-h5">
        @foreach ($items as $item)
            @if (! $loop->first)
                <li aria-hidden="true" class="text-brand font-bold select-none">\</li>
            @endif
            <li>
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="text-ink hover:text-brand transition">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-ink" @if ($loop->last) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
