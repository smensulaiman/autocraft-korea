@props(['vehicle', 'compact' => false])

<a href="{{ route('vehicles.show', $vehicle) }}"
   {{ $attributes->merge(['class' => 'card-enter group block border border-line bg-white transition-all duration-200 hover:-translate-y-0.5 hover:border-ink hover:shadow-lg']) }}>
    <div class="relative aspect-[4/3] overflow-hidden bg-ink/5 {{ $vehicle->thumbUrl() ? 'shimmer' : '' }}">
        @if ($vehicle->thumbUrl())
            <img src="{{ $vehicle->thumbUrl() }}" alt="{{ $vehicle->title() }}" loading="lazy" decoding="async"
                 class="relative h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
        @else
            <div class="grid h-full w-full place-items-center">
                <svg class="size-9 text-ink/15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 13l1.4-4.2A2 2 0 0 1 8.3 7.5h7.4a2 2 0 0 1 1.9 1.3L19 13M5 13h14M5 13v4.5h1.5M19 13v4.5h-1.5"/>
                    <circle cx="8" cy="17.5" r="1.5"/><circle cx="16" cy="17.5" r="1.5"/>
                </svg>
            </div>
        @endif
        <div class="absolute top-2 left-2 flex gap-1.5">
            @if ($vehicle->year)
                <span class="bg-ink px-1.5 py-0.5 font-mono text-[10px] font-semibold text-white">{{ $vehicle->year }}</span>
            @endif
            @if ($vehicle->is_verified && ! $compact)
                <span class="bg-signal px-1.5 py-0.5 font-mono text-[10px] font-semibold text-white">Verified</span>
            @endif
        </div>
    </div>

    <div class="{{ $compact ? 'p-3' : 'p-4' }}">
        <h3 class="truncate font-bold tracking-tight uppercase {{ $compact ? 'text-[13px]' : 'text-base' }}">
            {{ $vehicle->brand_name }} {{ $vehicle->model_name }}
        </h3>
        <p class="mt-0.5 truncate text-ink/60 {{ $compact ? 'text-[11.5px]' : 'text-[13px]' }}">
            {{ $vehicle->trim ?: '—' }}
        </p>
        <p class="mt-2 font-extrabold tracking-tight text-signal tabular-nums {{ $compact ? 'text-base' : 'text-xl' }}">
            @if ($vehicle->price_usd)
                FOB ${{ number_format($vehicle->displayPriceUsd()) }}
            @else
                <span class="text-[12px] font-bold text-ink/50">Price on request</span>
            @endif
        </p>
        <div class="mt-2 border-t border-line pt-2 font-mono tracking-wider text-ink/55 uppercase {{ $compact ? 'text-[9.5px]' : 'text-[10.5px]' }}">
            {{ $vehicle->mileage !== null ? number_format($vehicle->mileage).' km' : '— km' }}
            · {{ \App\Models\Vehicle::label($vehicle->fuel_type) }}
            · {{ \App\Models\Vehicle::label($vehicle->transmission) }}
        </div>
    </div>
</a>
