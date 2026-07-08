@props(['label' => '', 'title'])

<div class="mb-4 flex items-end gap-4">
    <div class="shrink-0">
        @if ($label !== '')
            <p class="microlabel">{{ $label }}</p>
        @endif
        <h2 class="mt-1 text-xl font-black tracking-tight uppercase">{{ $title }}</h2>
    </div>
    <div class="mb-1.5 hidden h-px flex-1 bg-line sm:block"></div>
    @if ($slot->isNotEmpty())
        <div class="mb-0.5 shrink-0">{{ $slot }}</div>
    @endif
</div>
