@props(['category'])

<a href="{{ route('catalog.category', $category->slug) }}"
   {{ $attributes->merge(['class' => 'group relative block overflow-hidden shape-card h-44']) }}>
    <img src="{{ asset('img/categories/'.$category->slug.'.svg') }}" alt=""
         class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-brand mix-blend-multiply opacity-85"></div>
    <div class="relative h-full flex items-center justify-center">
        <span class="type-h1 text-canvas">{{ $category->name }}</span>
    </div>
    <span class="absolute top-4 right-4 text-canvas transition group-hover:translate-x-1 group-hover:-translate-y-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M7 17L17 7M9 7h8v8"/>
        </svg>
    </span>
</a>
