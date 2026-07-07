<?php

use App\Models\Brand;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    private const ORIGIN_ORDER = [
        'korea' => 'Korea',
        'germany' => 'Germany',
        'japan' => 'Japan',
        'usa' => 'USA',
        'europe' => 'Europe',
        'china' => 'China',
        'other' => 'Other',
    ];

    private const COLOR_HEX = [
        'white' => '#ffffff', 'black' => '#1b1b1b', 'gray' => '#9aa0a6', 'silver' => '#cfd4da',
        'blue' => '#2563eb', 'red' => '#dc2626', 'green' => '#15803d', 'yellow' => '#eab308',
        'orange' => '#ea580c', 'brown' => '#7c4a21', 'beige' => '#d9c7a3', 'gold' => '#c9a227',
        'purple' => '#7c3aed', 'pink' => '#ec4899',
    ];

    // ── Applied filters (drive the results, synced to URL) ──────────────

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: [])]
    public array $brands = [];

    #[Url(except: [])]
    public array $models = [];

    #[Url(except: [])]
    public array $generations = [];

    #[Url(except: [])]
    public array $grades = [];

    #[Url(except: '')]
    public string $yearFrom = '';

    #[Url(except: '')]
    public string $yearTo = '';

    #[Url(except: '')]
    public string $mileageFrom = '';

    #[Url(except: '')]
    public string $mileageTo = '';

    #[Url(except: '')]
    public string $ccFrom = '';

    #[Url(except: '')]
    public string $ccTo = '';

    #[Url(except: [])]
    public array $transmissions = [];

    #[Url(except: [])]
    public array $fuels = [];

    #[Url(except: [])]
    public array $bodies = [];

    #[Url(except: [])]
    public array $regions = [];

    #[Url(except: [])]
    public array $colors = [];

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    #[Url(except: 'grid')]
    public string $view = 'grid';

    /** Set (e.g. via "View all" links) to force the results list without filters. */
    #[Url(except: '')]
    public string $show = '';

    /** Draft filter state edited in the hero panel; applied on "Search". */
    public array $draft = [];

    public function mount(): void
    {
        $this->draft = $this->appliedFilters();
    }

    public function updatedDraft($value, string $key): void
    {
        $root = explode('.', $key)[0];

        if ($root === 'brands') {
            $this->draft['models'] = [];
        }

        if (in_array($root, ['brands', 'models'], true)) {
            $this->draft['generations'] = [];
            $this->draft['grades'] = [];
        }
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function apply(): void
    {
        foreach ($this->draft as $key => $value) {
            $this->{$key} = $value;
        }

        if (! $this->hasActiveFilters()) {
            $this->show = 'all';
        }

        $this->resetPage();
        $this->dispatch('filters-applied');
    }

    public function toggleBrand(string $slug): void
    {
        $this->brands = in_array($slug, $this->brands, true)
            ? array_values(array_diff($this->brands, [$slug]))
            : [...$this->brands, $slug];

        $this->models = [];
        $this->generations = [];
        $this->grades = [];

        $this->draft = $this->appliedFilters();
        $this->resetPage();
        $this->dispatch('filters-applied');
    }

    public function resetAll(): void
    {
        $this->reset([
            'search', 'brands', 'models', 'generations', 'grades', 'yearFrom', 'yearTo',
            'mileageFrom', 'mileageTo', 'ccFrom', 'ccTo', 'transmissions', 'fuels',
            'bodies', 'regions', 'colors', 'sort', 'show',
        ]);

        $this->draft = $this->appliedFilters();
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        foreach ($this->appliedFilters() as $value) {
            if ($value !== '' && $value !== []) {
                return true;
            }
        }

        return false;
    }

    #[Computed]
    public function isBrowsing(): bool
    {
        return $this->show === '' && ! $this->hasActiveFilters();
    }

    public function label(?string $value): string
    {
        return Vehicle::label($value);
    }

    public function colorHex(string $color): string
    {
        return self::COLOR_HEX[$color] ?? '#9aa0a6';
    }

    // ── Results data ─────────────────────────────────────────────────────

    #[Computed]
    public function vehicles(): LengthAwarePaginator
    {
        $query = $this->buildQuery($this->appliedFilters());

        match ($this->sort) {
            'price_low' => $query->orderByRaw('price_usd is null, price_usd asc'),
            'price_high' => $query->orderByRaw('price_usd is null, price_usd desc'),
            'mileage_low' => $query->orderByRaw('mileage is null, mileage asc'),
            'year_new' => $query->orderByRaw('year is null, year desc'),
            'az' => $query->orderBy('brand_name')->orderBy('model_name'),
            default => $query->orderByDesc('first_seen_at'),
        };

        return $query->paginate(25);
    }

    #[Computed]
    public function previewCount(): int
    {
        return $this->buildQuery($this->draft)->count();
    }

    // ── Home sections (shown while no search/filter is active) ──────────

    #[Computed]
    public function newArrivals(): Collection
    {
        return $this->cachedSection('home.new-arrivals', fn (Builder $q) => $q
            ->orderByDesc('first_seen_at')
            ->limit(10));
    }

    #[Computed]
    public function featured(): Collection
    {
        return $this->cachedSection('home.featured', fn (Builder $q) => $q
            ->where('is_verified', true)
            ->whereBetween('price_usd', [30000, 250000])
            ->where('photos_count', '>=', 15)
            ->orderByDesc('first_seen_at')
            ->limit(5));
    }

    #[Computed]
    public function highGrade(): Collection
    {
        return $this->cachedSection('home.high-grade', fn (Builder $q) => $q
            ->where('is_verified', true)
            ->where('year', '>=', now()->year - 4)
            ->where('mileage', '<=', 50000)
            ->orderByDesc('year')
            ->limit(5));
    }

    #[Computed]
    public function budget(): Collection
    {
        return $this->cachedSection('home.budget', fn (Builder $q) => $q
            ->whereBetween('price_usd', [1500, 10000])
            ->whereNotNull('mileage')
            ->orderByDesc('first_seen_at')
            ->limit(5));
    }

    #[Computed]
    public function suvs(): Collection
    {
        return $this->cachedSection('home.suvs', fn (Builder $q) => $q
            ->where('body_type', 'suv')
            ->orderByDesc('first_seen_at')
            ->limit(5));
    }

    /**
     * Cache only vehicle IDs (plain data — models don't survive cache
     * serialization here) and hydrate fresh models per request.
     */
    private function cachedSection(string $key, callable $constraint): Collection
    {
        $ids = Cache::remember($key, 900, function () use ($constraint) {
            $query = Vehicle::available()->whereNotNull('thumb_url');
            $constraint($query);

            return $query->pluck('id')->all();
        });

        return Vehicle::query()
            ->findMany($ids)
            ->sortBy(fn (Vehicle $vehicle) => array_search($vehicle->id, $ids, true))
            ->values();
    }

    // ── Facets ───────────────────────────────────────────────────────────

    #[Computed]
    public function brandGroups(): array
    {
        return Cache::remember('vehicles.facets.brand-groups.v2', 3600, function () {
            $counts = Vehicle::available()
                ->whereNotNull('brand_slug')
                ->selectRaw('brand_slug, count(*) as total')
                ->groupBy('brand_slug')
                ->pluck('total', 'brand_slug');

            $brands = Brand::query()->orderBy('name')->get();
            $groups = [];

            foreach (self::ORIGIN_ORDER as $origin => $label) {
                if ($origin === 'other') {
                    continue;
                }

                $items = $brands
                    ->where('origin', $origin)
                    ->map(fn (Brand $brand) => [
                        'slug' => $brand->slug,
                        'name' => $brand->name,
                        'logo' => $brand->logo_url,
                        'total' => (int) ($counts[$brand->slug] ?? 0),
                    ])
                    ->filter(fn (array $item) => $item['total'] > 0)
                    ->sortByDesc('total')
                    ->values()
                    ->all();

                if ($items !== []) {
                    $groups[] = ['origin' => $origin, 'label' => $label, 'brands' => $items];
                }
            }

            return $groups;
        });
    }

    #[Computed]
    public function brandTiles(): array
    {
        return collect($this->brandGroups)
            ->flatMap(fn (array $group) => $group['brands'])
            ->filter(fn (array $brand) => $brand['logo'] !== null)
            ->sortByDesc('total')
            ->take(18)
            ->values()
            ->all();
    }

    #[Computed]
    public function modelGroups(): array
    {
        if (empty($this->draft['brands'])) {
            return [];
        }

        $brandSlugs = $this->draft['brands'];
        sort($brandSlugs);
        $cacheKey = 'vehicles.facets.models.'.md5(implode(',', $brandSlugs));

        return Cache::remember($cacheKey, 3600, fn () => Vehicle::available()
            ->whereIn('brand_slug', $brandSlugs)
            ->whereNotNull('model_slug')
            ->selectRaw('brand_slug, min(brand_name) as brand_name, model_slug, min(model_name) as model_name, count(*) as total')
            ->groupBy('brand_slug', 'model_slug')
            ->orderBy('brand_name')
            ->orderBy('model_name')
            ->get()
            ->groupBy('brand_name')
            ->map(fn ($models) => $models->map(fn ($row) => [
                'slug' => $row->model_slug,
                'name' => $row->model_name,
                'total' => (int) $row->total,
            ])->all())
            ->all());
    }

    #[Computed]
    public function generationOptions(): array
    {
        if (empty($this->draft['models'])) {
            return [];
        }

        return Vehicle::available()
            ->whereIn('model_slug', $this->draft['models'])
            ->whereNotNull('generation')
            ->distinct()
            ->orderBy('generation')
            ->pluck('generation')
            ->all();
    }

    #[Computed]
    public function gradeOptions(): array
    {
        if (empty($this->draft['models'])) {
            return [];
        }

        return Vehicle::available()
            ->whereIn('model_slug', $this->draft['models'])
            ->whereNotNull('trim')
            ->where('trim', '!=', '')
            ->distinct()
            ->orderBy('trim')
            ->limit(80)
            ->pluck('trim')
            ->all();
    }

    #[Computed]
    public function facets(): array
    {
        return Cache::remember('vehicles.facets.static.v2', 3600, fn () => [
            'transmissions' => $this->distinctValues('transmission'),
            'fuels' => $this->distinctValues('fuel_type'),
            'bodies' => $this->distinctValues('body_type'),
            'regions' => $this->distinctValues('region'),
            'colors' => array_values(array_intersect($this->distinctValues('color'), array_keys(self::COLOR_HEX))),
            'yearMin' => (int) (Vehicle::available()->min('year') ?? 2000),
            'yearMax' => (int) (Vehicle::available()->max('year') ?? now()->year),
        ]);
    }

    private function distinctValues(string $column): array
    {
        return Vehicle::available()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->whereNotIn($column, ['unknown', 'other'])
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    private function appliedFilters(): array
    {
        return [
            'search' => $this->search,
            'brands' => $this->brands,
            'models' => $this->models,
            'generations' => $this->generations,
            'grades' => $this->grades,
            'yearFrom' => $this->yearFrom,
            'yearTo' => $this->yearTo,
            'mileageFrom' => $this->mileageFrom,
            'mileageTo' => $this->mileageTo,
            'ccFrom' => $this->ccFrom,
            'ccTo' => $this->ccTo,
            'transmissions' => $this->transmissions,
            'fuels' => $this->fuels,
            'bodies' => $this->bodies,
            'regions' => $this->regions,
            'colors' => $this->colors,
        ];
    }

    private function buildQuery(array $f): Builder
    {
        $query = Vehicle::query()->available();

        if (($term = trim($f['search'] ?? '')) !== '') {
            $query->where(fn (Builder $q) => $q
                ->where('brand_name', 'like', "%{$term}%")
                ->orWhere('model_name', 'like', "%{$term}%")
                ->orWhere('trim', 'like', "%{$term}%"));
        }

        return $query
            ->when(! empty($f['brands']), fn (Builder $q) => $q->whereIn('brand_slug', $f['brands']))
            ->when(! empty($f['models']), fn (Builder $q) => $q->whereIn('model_slug', $f['models']))
            ->when(! empty($f['generations']), fn (Builder $q) => $q->whereIn('generation', $f['generations']))
            ->when(! empty($f['grades']), fn (Builder $q) => $q->whereIn('trim', $f['grades']))
            ->when(($f['yearFrom'] ?? '') !== '', fn (Builder $q) => $q->where('year', '>=', (int) $f['yearFrom']))
            ->when(($f['yearTo'] ?? '') !== '', fn (Builder $q) => $q->where('year', '<=', (int) $f['yearTo']))
            ->when(($f['mileageFrom'] ?? '') !== '', fn (Builder $q) => $q->where('mileage', '>=', (int) $f['mileageFrom']))
            ->when(($f['mileageTo'] ?? '') !== '', fn (Builder $q) => $q->where('mileage', '<=', (int) $f['mileageTo']))
            ->when(($f['ccFrom'] ?? '') !== '', fn (Builder $q) => $q->where('engine_cc', '>=', (int) $f['ccFrom']))
            ->when(($f['ccTo'] ?? '') !== '', fn (Builder $q) => $q->where('engine_cc', '<=', (int) $f['ccTo']))
            ->when(! empty($f['transmissions']), fn (Builder $q) => $q->whereIn('transmission', $f['transmissions']))
            ->when(! empty($f['fuels']), fn (Builder $q) => $q->whereIn('fuel_type', $f['fuels']))
            ->when(! empty($f['bodies']), fn (Builder $q) => $q->whereIn('body_type', $f['bodies']))
            ->when(! empty($f['regions']), fn (Builder $q) => $q->whereIn('region', $f['regions']))
            ->when(! empty($f['colors']), fn (Builder $q) => $q->whereIn('color', $f['colors']));
    }
};

?>

<div>

    {{-- ── Hero (compact, signal red) ───────────────────────────────── --}}
    <section class="relative z-20 bg-ink-soft text-white">
        <div class="pointer-events-none absolute inset-0 opacity-[0.05]"
             style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <div class="card-enter flex flex-wrap items-end justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="pulse-dot inline-block size-2 rounded-full bg-signal"></span>
                        <p class="font-mono text-[10.5px] tracking-[0.3em] text-white/75 uppercase">Live Korea inventory — updated daily</p>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight uppercase sm:text-3xl" style="font-stretch: 115%;">
                        Find your car direct from Korea
                    </h1>
                </div>
                <p class="font-mono text-[11px] tracking-widest text-white/75 uppercase tabular-nums">
                    {{ number_format($this->previewCount) }} units in stock
                </p>
            </div>

            {{-- Search + filter panel --}}
            <div class="card-enter mt-5 border border-ink/15 bg-white text-ink shadow-2xl" style="animation-delay: 90ms;" x-data="{ more: true }">
                <div class="border-b border-line p-4 pb-0">
                    <div class="relative">
                        <input
                            type="search"
                            wire:model.live.debounce.500ms="draft.search"
                            wire:keydown.enter="apply"
                            placeholder="Search brand, model, or grade — e.g. Sorento, G80, Avante"
                            class="field !h-12 !pr-16"
                        >
                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 font-mono text-[10px] tracking-widest text-ink/35 uppercase">↵</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 py-4 sm:grid-cols-2 lg:grid-cols-6">

                        {{-- Brands multi-select --}}
                        <div x-data="{ open: false }" x-on:click.outside="open = false" class="relative">
                            <label class="microlabel mb-1 block">Brands</label>
                            <button type="button" x-on:click="open = !open"
                                    class="field flex items-center justify-between gap-2 text-left">
                                <span class="truncate">
                                    @if (empty($draft['brands'])) All brands
                                    @else {{ count($draft['brands']) }} selected
                                    @endif
                                </span>
                                <svg class="size-3.5 shrink-0 text-ink/40" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                                 class="absolute z-40 mt-1 max-h-96 w-full min-w-64 overflow-y-auto border border-line bg-white shadow-xl">
                                @foreach ($this->brandGroups as $group)
                                    <p class="microlabel sticky top-0 border-b border-line bg-paper px-3 py-1.5">{{ $group['label'] }}</p>
                                    @foreach ($group['brands'] as $brandItem)
                                        <label class="flex cursor-pointer items-center gap-2.5 px-3 py-1.5 transition hover:bg-paper">
                                            <input type="checkbox" wire:model.live="draft.brands" value="{{ $brandItem['slug'] }}" class="accent-signal">
                                            @if ($brandItem['logo'])
                                                <img src="{{ $brandItem['logo'] }}" alt="" loading="lazy" class="h-5 w-7 object-contain">
                                            @else
                                                <span class="h-5 w-7"></span>
                                            @endif
                                            <span class="flex-1 truncate text-sm">{{ $brandItem['name'] }}</span>
                                            <span class="font-mono text-[10px] text-ink/40 tabular-nums">{{ number_format($brandItem['total']) }}</span>
                                        </label>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>

                        {{-- Models multi-select --}}
                        <div x-data="{ open: false }" x-on:click.outside="open = false" class="relative">
                            <label class="microlabel mb-1 block">Models</label>
                            <button type="button" x-on:click="open = !open"
                                    @if (empty($draft['brands'])) disabled @endif
                                    class="field flex items-center justify-between gap-2 text-left disabled:cursor-not-allowed disabled:opacity-50">
                                <span class="truncate">
                                    @if (empty($draft['brands'])) Select brand
                                    @elseif (empty($draft['models'])) All models
                                    @else {{ count($draft['models']) }} selected
                                    @endif
                                </span>
                                <svg class="size-3.5 shrink-0 text-ink/40" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                                 class="absolute z-40 mt-1 max-h-96 w-full min-w-64 overflow-y-auto border border-line bg-white shadow-xl">
                                @forelse ($this->modelGroups as $brandName => $modelItems)
                                    <p class="microlabel sticky top-0 border-b border-line bg-paper px-3 py-1.5">{{ $brandName }}</p>
                                    @foreach ($modelItems as $modelItem)
                                        <label class="flex cursor-pointer items-center gap-2.5 px-3 py-1.5 transition hover:bg-paper">
                                            <input type="checkbox" wire:model.live="draft.models" value="{{ $modelItem['slug'] }}" class="accent-signal">
                                            <span class="flex-1 truncate text-sm">{{ $modelItem['name'] }}</span>
                                            <span class="font-mono text-[10px] text-ink/40 tabular-nums">{{ number_format($modelItem['total']) }}</span>
                                        </label>
                                    @endforeach
                                @empty
                                    <p class="px-3 py-3 text-sm text-ink/50">Pick one or more brands first.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Chassis --}}
                        <x-filter-multiselect
                            label="Chassis"
                            model="draft.generations"
                            :options="collect($this->generationOptions)->mapWithKeys(fn ($v) => [$v => $v])->all()"
                            :selected="$draft['generations'] ?? []"
                            placeholder="All chassis"
                            :disabled="empty($this->generationOptions)"
                            :disabled-text="empty($draft['models']) ? 'Select model' : 'No data yet'"
                        />

                        {{-- Grade --}}
                        <x-filter-multiselect
                            label="Grade"
                            model="draft.grades"
                            :options="collect($this->gradeOptions)->mapWithKeys(fn ($v) => [$v => $v])->all()"
                            :selected="$draft['grades'] ?? []"
                            placeholder="All grades"
                            :disabled="empty($this->gradeOptions)"
                            :disabled-text="empty($draft['models']) ? 'Select model' : 'No data yet'"
                        />

                        {{-- Year range --}}
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="microlabel mb-1 block">Year</label>
                            <div class="flex items-center gap-2">
                                <select wire:model.live="draft.yearFrom" class="field">
                                    <option value="">From</option>
                                    @for ($y = $this->facets['yearMax']; $y >= $this->facets['yearMin']; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                                <select wire:model.live="draft.yearTo" class="field">
                                    <option value="">To</option>
                                    @for ($y = $this->facets['yearMax']; $y >= $this->facets['yearMin']; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- More filters (collapsible) --}}
                    <div x-show="more" x-collapse x-cloak>
                        <div class="grid grid-cols-1 gap-3 border-t border-line py-4 sm:grid-cols-2 lg:grid-cols-6">
                            <div>
                                <label class="microlabel mb-1 block">Mileage (km)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" min="0" step="10000" placeholder="Min" wire:model.live.debounce.600ms="draft.mileageFrom" class="field">
                                    <input type="number" min="0" step="10000" placeholder="Max" wire:model.live.debounce.600ms="draft.mileageTo" class="field">
                                </div>
                            </div>
                            <div>
                                <label class="microlabel mb-1 block">Engine (cc)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" min="0" step="100" placeholder="Min" wire:model.live.debounce.600ms="draft.ccFrom" class="field">
                                    <input type="number" min="0" step="100" placeholder="Max" wire:model.live.debounce.600ms="draft.ccTo" class="field">
                                </div>
                            </div>
                            <x-filter-multiselect
                                label="Transmission"
                                model="draft.transmissions"
                                :options="collect($this->facets['transmissions'])->mapWithKeys(fn ($v) => [$v => \App\Models\Vehicle::label($v)])->all()"
                                :selected="$draft['transmissions'] ?? []"
                            />
                            <x-filter-multiselect
                                label="Fuel"
                                model="draft.fuels"
                                :options="collect($this->facets['fuels'])->mapWithKeys(fn ($v) => [$v => \App\Models\Vehicle::label($v)])->all()"
                                :selected="$draft['fuels'] ?? []"
                            />
                            <x-filter-multiselect
                                label="Body type"
                                model="draft.bodies"
                                :options="collect($this->facets['bodies'])->mapWithKeys(fn ($v) => [$v => \App\Models\Vehicle::label($v)])->all()"
                                :selected="$draft['bodies'] ?? []"
                            />
                            <x-filter-multiselect
                                label="Region"
                                model="draft.regions"
                                :options="collect($this->facets['regions'])->mapWithKeys(fn ($v) => [$v => $v])->all()"
                                :selected="$draft['regions'] ?? []"
                            />
                            <div class="sm:col-span-2 lg:col-span-6">
                                <label class="microlabel mb-1 block">Color</label>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($this->facets['colors'] as $colorOption)
                                        <label class="flex cursor-pointer items-center gap-1.5 border px-2.5 py-1.5 text-[12px] transition select-none
                                            {{ in_array($colorOption, $draft['colors'] ?? [], true) ? 'border-signal bg-signal/5 font-semibold' : 'border-line hover:border-ink' }}">
                                            <input type="checkbox" wire:model.live="draft.colors" value="{{ $colorOption }}" class="sr-only">
                                            <span class="inline-block size-3 rounded-full border border-ink/20" style="background: {{ $this->colorHex($colorOption) }};"></span>
                                            {{ ucfirst($colorOption) }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel footer --}}
                <div class="flex flex-col items-stretch justify-between gap-3 bg-paper px-4 py-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-5">
                        <button type="button" x-on:click="more = !more"
                                class="font-mono text-[11px] tracking-widest text-ink/60 uppercase transition hover:text-signal"
                                x-text="more ? '− Less filters' : '+ More filters'"></button>
                        <button wire:click="resetAll"
                                class="font-mono text-[11px] tracking-widest text-ink/40 uppercase transition hover:text-signal">
                            Reset
                        </button>
                    </div>
                    <button wire:click="apply"
                            class="bg-ink px-8 py-3 font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase transition hover:bg-signal">
                        <span wire:loading.remove wire:target="apply, draft">
                            Search {{ number_format($this->previewCount) }} vehicles
                        </span>
                        <span wire:loading wire:target="apply, draft" class="inline-flex items-center gap-2">
                            <span class="pulse-dot inline-block size-2 rounded-full bg-white"></span>
                            Counting…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    @if ($this->isBrowsing)

        {{-- ── Curated home sections ────────────────────────────────── --}}
        <div class="mx-auto max-w-7xl space-y-12 px-4 py-10 sm:px-6" id="results">

            {{-- Browse by brand --}}
            <section>
                <x-section-heading label="// Browse by brand" title="Popular brands" />
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-6 lg:grid-cols-9">
                    @foreach ($this->brandTiles as $tile)
                        <button
                            wire:click="toggleBrand('{{ $tile['slug'] }}')"
                            title="{{ $tile['name'] }} ({{ number_format($tile['total']) }})"
                            class="group flex flex-col items-center gap-1.5 border border-line bg-white p-3 transition hover:border-signal"
                        >
                            <img src="{{ $tile['logo'] }}" alt="{{ $tile['name'] }}" loading="lazy"
                                 class="h-8 w-full object-contain grayscale transition-all duration-300 group-hover:scale-110 group-hover:grayscale-0">
                            <span class="w-full truncate text-center font-mono text-[9px] tracking-wide text-ink/50 uppercase">{{ $tile['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- New arrivals --}}
            @if ($this->newArrivals->isNotEmpty())
                <section>
                    <x-section-heading label="// Fresh from Korea" title="New arrivals">
                        <button wire:click="$set('show', 'all')" class="font-mono text-[11px] tracking-widest text-signal uppercase hover:underline">
                            View all →
                        </button>
                    </x-section-heading>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($this->newArrivals as $vehicle)
                            <x-vehicle-card :vehicle="$vehicle" :compact="true"
                                            wire:key="new-{{ $vehicle->id }}"
                                            style="animation-delay: {{ min($loop->index, 9) * 40 }}ms" />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Featured premium --}}
            @if ($this->featured->count() >= 3)
                <section>
                    <x-section-heading label="// Hand-picked stock" title="Premium picks">
                        <a href="{{ route('vehicles.index', ['priceFrom' => 30000, 'sort' => 'price_high']) }}"
                           class="font-mono text-[11px] tracking-widest text-signal uppercase hover:underline">
                            View all →
                        </a>
                    </x-section-heading>

                    @php $hero = $this->featured->first(); @endphp
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                        {{-- Big featured card --}}
                        <a href="{{ route('vehicles.show', $hero) }}"
                           class="card-enter group relative block overflow-hidden border border-line bg-ink">
                            <div class="aspect-[16/11] overflow-hidden">
                                <img src="{{ $hero->thumbUrl() }}" alt="{{ $hero->title() }}" loading="lazy"
                                     class="h-full w-full object-cover opacity-90 transition-transform duration-700 group-hover:scale-[1.05]">
                            </div>
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink via-ink/70 to-transparent p-5 pt-16 text-white">
                                <p class="font-mono text-[10.5px] tracking-widest text-white/70 uppercase">
                                    {{ $hero->year }} · {{ number_format($hero->mileage ?? 0) }} km · {{ \App\Models\Vehicle::label($hero->fuel_type) }}
                                </p>
                                <h3 class="mt-1 text-2xl font-black tracking-tight uppercase">{{ $hero->brand_name }} {{ $hero->model_name }}</h3>
                                <p class="mt-0.5 truncate text-sm text-white/70">{{ $hero->trim }}</p>
                                <p class="mt-2 text-2xl font-black text-signal tabular-nums">${{ number_format($hero->price_usd) }}</p>
                            </div>
                        </a>

                        {{-- Side grid --}}
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($this->featured->skip(1)->take(4) as $vehicle)
                                <x-vehicle-card :vehicle="$vehicle" :compact="true"
                                                wire:key="feat-{{ $vehicle->id }}"
                                                style="animation-delay: {{ $loop->index * 60 }}ms" />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- High grade --}}
            @if ($this->highGrade->isNotEmpty())
                <section>
                    <x-section-heading label="// Verified · low mileage · recent year" title="High grade">
                        <a href="{{ route('vehicles.index', ['yearFrom' => now()->year - 4, 'mileageTo' => 50000]) }}"
                           class="font-mono text-[11px] tracking-widest text-signal uppercase hover:underline">
                            View all →
                        </a>
                    </x-section-heading>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($this->highGrade as $vehicle)
                            <x-vehicle-card :vehicle="$vehicle" :compact="true"
                                            wire:key="hg-{{ $vehicle->id }}"
                                            style="animation-delay: {{ $loop->index * 40 }}ms" />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Budget picks --}}
            @if ($this->budget->isNotEmpty())
                <section>
                    <x-section-heading label="// Under $10,000" title="Budget deals">
                        <a href="{{ route('vehicles.index', ['priceTo' => 10000, 'sort' => 'price_low']) }}"
                           class="font-mono text-[11px] tracking-widest text-signal uppercase hover:underline">
                            View all →
                        </a>
                    </x-section-heading>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($this->budget as $vehicle)
                            <x-vehicle-card :vehicle="$vehicle" :compact="true"
                                            wire:key="bd-{{ $vehicle->id }}"
                                            style="animation-delay: {{ $loop->index * 40 }}ms" />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- SUV collection --}}
            @if ($this->suvs->isNotEmpty())
                <section>
                    <x-section-heading label="// Family &amp; off-road ready" title="SUV collection">
                        <a href="{{ route('vehicles.index', ['bodies' => ['suv']]) }}"
                           class="font-mono text-[11px] tracking-widest text-signal uppercase hover:underline">
                            View all →
                        </a>
                    </x-section-heading>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($this->suvs as $vehicle)
                            <x-vehicle-card :vehicle="$vehicle" :compact="true"
                                            wire:key="suv-{{ $vehicle->id }}"
                                            style="animation-delay: {{ $loop->index * 40 }}ms" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

    @else

        {{-- ── Search results ───────────────────────────────────────── --}}
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6" id="results">
            <div class="flex gap-6">

                {{-- Brand logo quick-filter sidebar --}}
                <aside class="hidden w-56 shrink-0 lg:block">
                    <div class="sticky top-6 border border-line bg-white">
                        <div class="flex items-center justify-between border-b border-line px-3 py-2.5">
                            <span class="microlabel">// Brands</span>
                            @if (! empty($brands))
                                <button wire:click="$set('brands', [])"
                                        class="font-mono text-[10px] tracking-widest text-signal uppercase hover:underline">
                                    Clear
                                </button>
                            @endif
                        </div>
                        <div class="grid grid-cols-3 gap-1.5 p-2.5">
                            @foreach ($this->brandTiles as $tile)
                                <button
                                    wire:click="toggleBrand('{{ $tile['slug'] }}')"
                                    title="{{ $tile['name'] }} ({{ number_format($tile['total']) }})"
                                    class="group flex flex-col items-center gap-1 border p-1.5 transition
                                        {{ in_array($tile['slug'], $brands, true) ? 'border-signal bg-signal/5' : 'border-line hover:border-ink' }}"
                                >
                                    <img src="{{ $tile['logo'] }}" alt="{{ $tile['name'] }}" loading="lazy"
                                         class="h-7 w-full object-contain transition-all duration-300 group-hover:grayscale-0 {{ in_array($tile['slug'], $brands, true) ? '' : 'grayscale' }}">
                                    <span class="w-full truncate text-center font-mono text-[8.5px] tracking-wide text-ink/50 uppercase">{{ $tile['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </aside>

                {{-- Result list --}}
                <div class="min-w-0 flex-1">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <p class="microlabel">
                            {{ number_format($this->vehicles->total()) }} vehicles found
                            <button wire:click="resetAll" class="ml-2 text-signal normal-case hover:underline">clear &amp; back to home</button>
                        </p>

                        <div class="flex items-center gap-3">
                            <div class="flex border border-line bg-white">
                                <button wire:click="$set('view', 'grid')" title="Grid view"
                                        class="grid size-9 place-items-center transition {{ $view === 'grid' ? 'bg-ink text-white' : 'text-ink/40 hover:text-ink' }}">
                                    <svg class="size-4" viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="1" width="6" height="6"/><rect x="9" y="1" width="6" height="6"/><rect x="1" y="9" width="6" height="6"/><rect x="9" y="9" width="6" height="6"/></svg>
                                </button>
                                <button wire:click="$set('view', 'list')" title="List view"
                                        class="grid size-9 place-items-center transition {{ $view === 'list' ? 'bg-ink text-white' : 'text-ink/40 hover:text-ink' }}">
                                    <svg class="size-4" viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="2" width="14" height="3"/><rect x="1" y="7" width="14" height="3"/><rect x="1" y="12" width="14" height="3"/></svg>
                                </button>
                            </div>

                            <select wire:model.live="sort" class="field !w-auto">
                                <option value="newest">Newest arrivals</option>
                                <option value="price_low">Price — low to high</option>
                                <option value="price_high">Price — high to low</option>
                                <option value="mileage_low">Mileage — lowest</option>
                                <option value="year_new">Year — newest</option>
                                <option value="az">A → Z</option>
                            </select>
                        </div>
                    </div>

                    <div class="relative">
                        <div wire:loading.delay class="absolute inset-x-0 -top-2 z-10 h-0.5 overflow-hidden bg-line">
                            <div class="loading-bar h-full w-1/3 bg-signal"></div>
                        </div>

                        <div wire:loading.delay.class="animate-pulse opacity-50" class="transition-opacity duration-200">
                            @if ($this->vehicles->isEmpty())
                                <div class="border border-line bg-white px-6 py-20 text-center">
                                    <p class="font-mono text-xs tracking-[0.3em] text-ink/40 uppercase">// No match</p>
                                    <p class="mt-3 text-lg font-bold uppercase">No vehicles match these filters</p>
                                    <button wire:click="resetAll"
                                            class="mt-6 inline-block bg-ink px-6 py-3 font-mono text-xs tracking-widest text-white uppercase transition hover:bg-signal">
                                        Reset all filters
                                    </button>
                                </div>
                            @elseif ($view === 'list')
                                <div class="space-y-3">
                                    @foreach ($this->vehicles as $vehicle)
                                        <a href="{{ route('vehicles.show', $vehicle) }}"
                                           wire:key="list-{{ $vehicle->id }}"
                                           class="card-enter group flex border border-line bg-white transition-colors duration-200 hover:border-ink"
                                           style="animation-delay: {{ min($loop->index, 8) * 40 }}ms">
                                            <div class="relative aspect-[4/3] w-40 shrink-0 overflow-hidden bg-ink/5 sm:w-56">
                                                @if ($vehicle->thumbUrl())
                                                    <img src="{{ $vehicle->thumbUrl() }}" alt="{{ $vehicle->title() }}" loading="lazy"
                                                         class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
                                                @else
                                                    <div class="grid h-full w-full place-items-center font-mono text-[10px] tracking-widest text-ink/30 uppercase">No photo</div>
                                                @endif
                                                @if ($vehicle->year)
                                                    <span class="absolute top-2 left-2 bg-ink px-2 py-0.5 font-mono text-[10.5px] font-semibold text-white">{{ $vehicle->year }}</span>
                                                @endif
                                            </div>
                                            <div class="flex min-w-0 flex-1 flex-col justify-between p-4">
                                                <div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <h3 class="truncate text-base font-bold tracking-tight uppercase">
                                                            {{ $vehicle->brand_name }} {{ $vehicle->model_name }}
                                                        </h3>
                                                        @if ($vehicle->is_verified)
                                                            <span class="shrink-0 bg-signal px-2 py-0.5 font-mono text-[10px] font-semibold text-white uppercase">Verified</span>
                                                        @endif
                                                    </div>
                                                    <p class="mt-0.5 truncate text-[13px] text-ink/60">{{ $vehicle->trim ?: '—' }}</p>
                                                </div>
                                                <div class="mt-3 flex flex-wrap items-end justify-between gap-2">
                                                    <span class="font-mono text-[10.5px] tracking-wider text-ink/55 uppercase">
                                                        {{ $vehicle->mileage !== null ? number_format($vehicle->mileage).' km' : '— km' }}
                                                        · {{ $this->label($vehicle->fuel_type) }}
                                                        · {{ $this->label($vehicle->transmission) }}
                                                        @if ($vehicle->region) · {{ $vehicle->region }} @endif
                                                    </span>
                                                    <span class="text-xl font-extrabold tracking-tight text-signal tabular-nums">
                                                        @if ($vehicle->price_usd)
                                                            ${{ number_format($vehicle->price_usd) }}
                                                        @else
                                                            <span class="text-sm font-bold text-ink/50">Price on request</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                                    @foreach ($this->vehicles as $vehicle)
                                        <x-vehicle-card :vehicle="$vehicle" :compact="true"
                                                        wire:key="grid-{{ $vehicle->id }}"
                                                        style="animation-delay: {{ min($loop->index, 8) * 40 }}ms" />
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8">
                        {{ $this->vehicles->links('partials.pagination') }}
                    </div>
                </div>
            </div>
        </section>

    @endif
</div>

<script>
    $wire.on('filters-applied', () => {
        document.getElementById('results')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
