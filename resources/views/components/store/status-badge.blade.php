@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-pill px-3 py-1 type-body text-xs font-bold uppercase tracking-wide '.$status->badgeClasses()]) }}>
    {{ $status->label() }}
</span>
