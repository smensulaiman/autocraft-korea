<?php

use App\Models\Vehicle;
use App\Services\CarapisClient;
use App\Services\VehicleMapper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Vehicle $vehicle;

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;

        if (! $vehicle->hasDetails()) {
            try {
                $detail = app(CarapisClient::class)->vehicle($vehicle->id);
                $vehicle->fill(VehicleMapper::detailAttributes($detail))->save();
            } catch (\Throwable $e) {
                report($e); // Page still renders with catalog-level data.
            }
        }
    }

    /**
     * Same model first (closest in price), topped up with same-brand stock.
     */
    #[Computed]
    public function similar(): Collection
    {
        $base = fn () => Vehicle::available()
            ->whereKeyNot($this->vehicle->id)
            ->whereNotNull('thumb_url');

        $sameModel = $base()
            ->where('model_slug', $this->vehicle->model_slug)
            ->when($this->vehicle->price_usd, fn ($q) => $q->orderByRaw('abs(cast(price_usd as signed) - ?)', [$this->vehicle->price_usd]))
            ->orderByDesc('first_seen_at')
            ->limit(5)
            ->get();

        if ($sameModel->count() >= 5) {
            return $sameModel;
        }

        $sameBrand = $base()
            ->where('brand_slug', $this->vehicle->brand_slug)
            ->where('model_slug', '!=', $this->vehicle->model_slug)
            ->when($this->vehicle->body_type, fn ($q) => $q->orderByRaw('body_type = ? desc', [$this->vehicle->body_type]))
            ->orderByDesc('first_seen_at')
            ->limit(5 - $sameModel->count())
            ->get();

        return $sameModel->concat($sameBrand);
    }

    public function descriptionHtml(): ?string
    {
        if (! $this->vehicle->description) {
            return null;
        }

        return Str::markdown($this->vehicle->description, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function specs(): array
    {
        $vehicle = $this->vehicle;

        return array_filter([
            'Mileage' => $vehicle->mileage !== null ? number_format($vehicle->mileage).' km' : null,
            'Engine' => $vehicle->engine_cc ? number_format($vehicle->engine_cc).' cc' : null,
            'Fuel' => $vehicle->fuel_type && $vehicle->fuel_type !== 'unknown' ? ucfirst($vehicle->fuel_type) : null,
            'Transmission' => $vehicle->transmission && $vehicle->transmission !== 'unknown' ? ucfirst($vehicle->transmission) : null,
            'Drivetrain' => $vehicle->drive_type ? strtoupper($vehicle->drive_type) : null,
            'Body' => $vehicle->body_type && $vehicle->body_type !== 'unknown' ? ucfirst($vehicle->body_type) : null,
            'Color' => $vehicle->color ? ucfirst($vehicle->color) : null,
            'Seats' => $vehicle->seat_count ? $vehicle->seat_count.' seats' : null,
            'Generation' => $vehicle->generation,
            'Owners' => $vehicle->owner_count !== null ? (string) $vehicle->owner_count : null,
            'Warranty' => $vehicle->warranty_type,
            'Region' => $vehicle->region,
        ]);
    }
};

?>

<div>
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6">

        <a href="{{ route('vehicles.index') }}"
           class="inline-flex items-center gap-2 border border-line bg-white px-3 py-2 font-mono text-[11px] tracking-widest text-ink/60 uppercase transition hover:border-ink hover:text-ink">
            &larr; Back to stock
        </a>

        <div class="mt-4 flex flex-col gap-8 lg:flex-row">

            {{-- Left: gallery + description --}}
            <div class="min-w-0 flex-1">
                @php $photos = $vehicle->photoUrls(); @endphp

                <div
                    x-data="{
                        active: 0,
                        count: {{ count($photos) }},
                        next() { this.active = (this.active + 1) % this.count },
                        prev() { this.active = (this.active - 1 + this.count) % this.count },
                    }"
                    x-on:keydown.arrow-right.window="next()"
                    x-on:keydown.arrow-left.window="prev()"
                    class="border border-line bg-white"
                >
                    <div class="relative aspect-[4/3] overflow-hidden bg-ink/5 sm:aspect-[16/10]">
                        @forelse ($photos as $index => $photo)
                            <img
                                src="{{ $photo['url'] }}"
                                alt="{{ $vehicle->title() }} — photo {{ $index + 1 }}"
                                x-show="active === {{ $index }}"
                                x-cloak
                                @if ($index > 0) loading="lazy" @endif
                                class="absolute inset-0 h-full w-full object-cover"
                            >
                        @empty
                            <div class="grid h-full w-full place-items-center font-mono text-xs tracking-widest text-ink/30 uppercase">
                                No photos available
                            </div>
                        @endforelse

                        @if (count($photos) > 1)
                            <button x-on:click="prev()"
                                    class="absolute top-1/2 left-3 -translate-y-1/2 bg-ink/70 px-3 py-2 font-mono text-white transition hover:bg-signal"
                                    aria-label="Previous photo">&larr;</button>
                            <button x-on:click="next()"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 bg-ink/70 px-3 py-2 font-mono text-white transition hover:bg-signal"
                                    aria-label="Next photo">&rarr;</button>

                            <span class="absolute right-3 bottom-3 bg-ink/80 px-2.5 py-1 font-mono text-[11px] text-white tabular-nums">
                                <span x-text="active + 1"></span> / {{ count($photos) }}
                            </span>
                        @endif
                    </div>

                    @if (count($photos) > 1)
                        <div class="flex gap-1.5 overflow-x-auto border-t border-line p-2">
                            @foreach ($photos as $index => $photo)
                                <button
                                    x-on:click="active = {{ $index }}"
                                    :class="active === {{ $index }} ? 'border-signal' : 'border-transparent opacity-60 hover:opacity-100'"
                                    class="aspect-[4/3] w-20 shrink-0 overflow-hidden border-2 transition"
                                >
                                    <img src="{{ $photo['thumb'] }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($this->descriptionHtml())
                    <div class="mt-6 border border-line bg-white p-6">
                        <p class="microlabel mb-4">// Vehicle report</p>
                        <div class="manifest-prose">{!! $this->descriptionHtml() !!}</div>
                    </div>
                @endif

                @if (! empty($vehicle->features))
                    <div class="mt-6 border border-line bg-white p-6">
                        <p class="microlabel mb-4">// Equipment</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($vehicle->features as $feature)
                                <span class="border border-line bg-paper px-2.5 py-1 text-[12px]">
                                    {{ is_array($feature) ? ($feature['name'] ?? '') : $feature }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right: manifest panel --}}
            <aside class="w-full shrink-0 lg:w-96">
                <div class="border border-line border-t-2 border-t-signal bg-white shadow-sm lg:sticky lg:top-6">
                    <div class="border-b border-line p-5">
                        <div class="flex items-start justify-between gap-3">
                            <p class="microlabel">
                                @if ($vehicle->year) {{ $vehicle->year }} · @endif
                                Stock #{{ $vehicle->listing_id ?: Str::limit($vehicle->id, 8, '') }}
                            </p>
                            @if ($vehicle->brand?->logo_url)
                                <img src="{{ $vehicle->brand->logo_url }}"
                                     alt="{{ $vehicle->brand_name }} logo"
                                     loading="lazy"
                                     class="h-10 w-auto shrink-0 object-contain">
                            @endif
                        </div>
                        <h1 class="mt-2 text-2xl font-black tracking-tight uppercase" style="font-stretch: 110%;">
                            {{ $vehicle->brand_name }} {{ $vehicle->model_name }}
                        </h1>
                        @if ($vehicle->trim)
                            <p class="mt-1 text-sm text-ink/60">{{ $vehicle->trim }}</p>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @if ($vehicle->is_verified)
                                <span class="bg-signal px-2 py-1 font-mono text-[10px] font-semibold tracking-wider text-white uppercase">Verified</span>
                            @endif
                            @if ($vehicle->inspection_passed)
                                <span class="bg-ink px-2 py-1 font-mono text-[10px] font-semibold tracking-wider text-white uppercase">Inspection passed</span>
                            @endif
                            @if ($vehicle->has_accident === false)
                                <span class="bg-ink px-2 py-1 font-mono text-[10px] font-semibold tracking-wider text-white uppercase">No accident</span>
                            @endif
                            @if (! $vehicle->is_available)
                                <span class="bg-ink/40 px-2 py-1 font-mono text-[10px] font-semibold tracking-wider text-white uppercase">Sold / delisted</span>
                            @endif
                        </div>
                    </div>

                    <div class="border-b border-line p-5">
                        <p class="microlabel">// Price</p>
                        <p class="mt-1 text-4xl font-black tracking-tight text-signal tabular-nums">
                            @if ($vehicle->price_usd)
                                ${{ number_format($vehicle->price_usd) }}
                            @else
                                <span class="text-xl text-ink/50">Price on request</span>
                            @endif
                        </p>
                        @if ($vehicle->price_original)
                            <p class="mt-1 font-mono text-xs text-ink/50 tabular-nums">
                                ₩{{ number_format((float) $vehicle->price_original) }} {{ $vehicle->price_original_currency }} in Korea
                            </p>
                        @endif

                        <a
                            href="mailto:sales@autocraftjapan.com?subject={{ rawurlencode('Inquiry: '.$vehicle->title().' (stock '.($vehicle->listing_id ?: $vehicle->id).')') }}"
                            class="mt-5 block bg-ink py-3.5 text-center font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase transition hover:bg-signal"
                        >
                            Request quote
                        </a>
                    </div>

                    <div class="p-5">
                        <p class="microlabel mb-3">// Specification</p>
                        <dl class="divide-y divide-line/60">
                            @foreach ($this->specs() as $label => $value)
                                <div class="-mx-2 flex items-baseline justify-between gap-4 px-2 py-2 transition-colors hover:bg-paper">
                                    <dt class="font-mono text-[11px] tracking-wider text-ink/50 uppercase">{{ $label }}</dt>
                                    <dd class="text-right text-sm font-semibold">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        @if ($vehicle->vin || $vehicle->vehicle_no)
                            <div class="mt-4 border border-line bg-paper p-3 font-mono text-[11px] leading-relaxed text-ink/60">
                                @if ($vehicle->vin)
                                    <p>VIN — {{ $vehicle->vin }}</p>
                                @endif
                                @if ($vehicle->vehicle_no)
                                    <p>PLATE — {{ $vehicle->vehicle_no }}</p>
                                @endif
                                <p>REF — {{ strtoupper(Str::limit($vehicle->id, 13, '')) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>

        {{-- Similar vehicles --}}
        @if ($this->similar->isNotEmpty())
            <section class="mt-12">
                <x-section-heading label="// You may also like" title="Similar vehicles">
                    <a href="{{ route('vehicles.index', ['brands' => [$vehicle->brand_slug]]) }}"
                       class="font-mono text-[11px] tracking-widest text-signal uppercase hover:underline">
                        More {{ $vehicle->brand_name }} →
                    </a>
                </x-section-heading>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($this->similar as $similarVehicle)
                        <x-vehicle-card :vehicle="$similarVehicle" :compact="true"
                                        wire:key="sim-{{ $similarVehicle->id }}"
                                        style="animation-delay: {{ $loop->index * 40 }}ms" />
                    @endforeach
                </div>
            </section>
        @endif
    </section>
</div>
