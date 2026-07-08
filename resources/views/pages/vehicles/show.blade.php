<?php

use App\Models\Inquiry;
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

    public bool $showContactModal = false;

    public bool $inquirySent = false;

    public string $contactName = '';

    public string $contactEmail = '';

    public string $contactPhone = '';

    public string $contactMessage = '';

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

    public function openContactModal(): void
    {
        $this->showContactModal = true;
        $this->inquirySent = false;
    }

    public function closeContactModal(): void
    {
        $this->showContactModal = false;
    }

    public function sendInquiry(): void
    {
        $data = $this->validate([
            'contactName' => ['required', 'string', 'max:120'],
            'contactEmail' => ['required', 'email', 'max:190'],
            'contactPhone' => ['nullable', 'string', 'max:40'],
            'contactMessage' => ['nullable', 'string', 'max:2000'],
        ]);

        Inquiry::create([
            'vehicle_id' => $this->vehicle->id,
            'name' => $data['contactName'],
            'email' => $data['contactEmail'],
            'phone' => $data['contactPhone'],
            'message' => $data['contactMessage'],
        ]);

        // TODO: send a notification email once mail is configured.

        $this->reset(['contactName', 'contactEmail', 'contactPhone', 'contactMessage']);
        $this->inquirySent = true;
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

        <div class="mt-4 flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-stretch">

            {{-- Left: gallery --}}
            <div class="min-w-0">
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
                    x-effect="$refs.thumbs?.children[active]?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' })"
                    class="border border-line bg-white lg:flex lg:h-full lg:flex-col"
                >
                    <div class="relative aspect-[4/3] overflow-hidden bg-ink/5 sm:aspect-[16/10] lg:aspect-auto lg:min-h-0 lg:flex-1 lg:basis-0 {{ count($photos) ? 'shimmer' : '' }}">
                        @forelse ($photos as $index => $photo)
                            <img
                                src="{{ $photo['url'] }}"
                                alt="{{ $vehicle->title() }} — photo {{ $index + 1 }}"
                                x-show="active === {{ $index }}"
                                x-cloak
                                x-transition.opacity.duration.200ms
                                @if ($index > 0) loading="lazy" @else fetchpriority="high" @endif
                                decoding="async"
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
                        <div x-ref="thumbs"
                             x-on:wheel.prevent="$el.scrollLeft += $event.deltaY + $event.deltaX"
                             class="slim-scrollbar grid grid-flow-col grid-rows-2 justify-start gap-1.5 overflow-x-auto border-t border-line p-2 lg:shrink-0">
                            @foreach ($photos as $index => $photo)
                                <button
                                    x-on:click="active = {{ $index }}"
                                    :class="active === {{ $index }} ? 'border-signal' : 'border-transparent opacity-60 hover:opacity-100'"
                                    class="shimmer aspect-[4/3] w-20 overflow-hidden border-2 bg-ink/5 transition"
                                    aria-label="Photo {{ $index + 1 }}"
                                >
                                    <img src="{{ $photo['thumb'] }}" alt="" loading="lazy" decoding="async" class="relative h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: manifest panel --}}
            <aside class="w-full min-w-0">
                <div class="border border-line border-t-2 border-t-signal bg-white shadow-sm lg:flex lg:h-full lg:flex-col">
                    <div class="shrink-0 border-b border-line p-5">
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

                    <div class="shrink-0 border-b border-line p-5">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="microlabel">Price</p>
                            @if ($vehicle->price_usd)
                                <span class="border border-line bg-paper px-2 py-0.5 font-mono text-[10px] tracking-widest text-ink/60 uppercase">FOB</span>
                            @endif
                        </div>
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

                        <button
                            type="button"
                            wire:click="openContactModal"
                            class="mt-5 block w-full bg-ink py-3.5 text-center font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase transition hover:bg-signal"
                        >
                            Contact sales agent
                        </button>
                    </div>

                    <div class="p-5 lg:flex lg:min-h-0 lg:flex-1 lg:flex-col">
                        <p class="microlabel mb-3">Specification</p>
                        @php $specs = array_slice($this->specs(), 0, 8, true); @endphp
                        <dl class="grid grid-cols-2 gap-px border border-line bg-line">
                            @foreach ($specs as $label => $value)
                                <div class="min-w-0 bg-white px-3 py-2 transition-colors hover:bg-paper">
                                    <dt class="font-mono text-[10px] tracking-wider text-ink/45 uppercase">{{ $label }}</dt>
                                    <dd class="mt-0.5 truncate text-[13px] font-semibold" title="{{ $value }}">{{ $value }}</dd>
                                </div>
                            @endforeach
                            @if (count($specs) % 2 === 1)
                                <div class="bg-white"></div>
                            @endif
                        </dl>

                        @if ($vehicle->vin || $vehicle->vehicle_no)
                            <div class="lg:mt-auto">
                                <div class="mt-4 border border-line bg-paper p-3 font-mono text-[11px] leading-relaxed text-ink/60">
                                    @if ($vehicle->vin)
                                        <p>VIN — {{ $vehicle->vin }}</p>
                                    @endif
                                    @if ($vehicle->vehicle_no)
                                        <p>PLATE — {{ $vehicle->vehicle_no }}</p>
                                    @endif
                                    <p>REF — {{ strtoupper(Str::limit($vehicle->id, 13, '')) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>

        {{-- Equipment --}}
        @if (! empty($vehicle->features))
            <div class="mt-8 border border-line bg-white p-6">
                <p class="microlabel mb-4">Equipment</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($vehicle->features as $feature)
                        <span class="border border-line bg-paper px-2.5 py-1 text-[12px]">
                            {{ is_array($feature) ? ($feature['name'] ?? '') : $feature }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Similar vehicles --}}
        @if ($this->similar->isNotEmpty())
            <section class="mt-12">
                <x-section-heading label="You may also like" title="Similar vehicles">
                    <a href="{{ route('vehicles.index', ['brands' => [$vehicle->brand_slug]]) }}"
                       class="bg-ink px-5 py-2.5 font-mono text-[11px] tracking-widest text-white uppercase transition hover:bg-signal">
                        More {{ $vehicle->brand_name }}
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

    {{-- Contact sales agent modal --}}
    @if ($showContactModal)
        <div
            x-data
            x-trap.noscroll="true"
            x-on:keydown.escape.window="$wire.closeContactModal()"
            class="fixed inset-0 z-50 grid place-items-center bg-ink/70 p-4"
        >
            <div
                x-on:click.outside="$wire.closeContactModal()"
                class="w-full max-w-md border border-line bg-white shadow-2xl"
            >
                <div class="flex items-center justify-between border-b border-line p-5">
                    <div>
                        <p class="microlabel">Inquiry</p>
                        <h3 class="mt-1 text-lg font-black tracking-tight uppercase">Contact sales agent</h3>
                    </div>
                    <button type="button" wire:click="closeContactModal" aria-label="Close"
                            class="grid size-8 shrink-0 place-items-center border border-line text-ink/50 transition hover:border-ink hover:text-ink">
                        &times;
                    </button>
                </div>

                <div class="p-5">
                    @if ($inquirySent)
                        <div class="border border-line bg-paper p-6 text-center">
                            <p class="text-sm font-semibold">Thanks — your inquiry has been sent.</p>
                            <p class="mt-1 text-[13px] text-ink/60">Our sales team will reach out to you shortly about the {{ $vehicle->title() }}.</p>
                            <button type="button" wire:click="closeContactModal"
                                    class="mt-5 bg-ink px-6 py-2.5 font-mono text-[11px] tracking-widest text-white uppercase transition hover:bg-signal">
                                Close
                            </button>
                        </div>
                    @else
                        <p class="mb-4 text-[13px] text-ink/60">
                            {{ $vehicle->title() }} — Stock #{{ $vehicle->listing_id ?: Str::limit($vehicle->id, 8, '') }}
                        </p>

                        <form wire:submit="sendInquiry" class="space-y-3">
                            <div>
                                <label class="microlabel mb-1 block">Name</label>
                                <input type="text" wire:model="contactName" class="field" placeholder="Your full name">
                                @error('contactName') <p class="mt-1 text-[11px] text-signal">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="microlabel mb-1 block">Email</label>
                                <input type="email" wire:model="contactEmail" class="field" placeholder="you@example.com">
                                @error('contactEmail') <p class="mt-1 text-[11px] text-signal">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="microlabel mb-1 block">Phone (optional)</label>
                                <input type="text" wire:model="contactPhone" class="field" placeholder="+81 90 0000 0000">
                                @error('contactPhone') <p class="mt-1 text-[11px] text-signal">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="microlabel mb-1 block">Message (optional)</label>
                                <textarea wire:model="contactMessage" rows="4" class="field" placeholder="Any questions about this vehicle..."></textarea>
                                @error('contactMessage') <p class="mt-1 text-[11px] text-signal">{{ $message }}</p> @enderror
                            </div>

                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="sendInquiry"
                                class="mt-2 block w-full bg-ink py-3.5 text-center font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase transition hover:bg-signal disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="sendInquiry">Send</span>
                                <span wire:loading wire:target="sendInquiry">Sending…</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
