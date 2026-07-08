<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Hero Filter — Design Lab</title>

    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper font-sans text-ink antialiased">

    {{-- Picker bar --}}
    <div class="sticky top-0 z-50 border-b border-line bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <p class="font-mono text-[11px] tracking-widest text-ink/60 uppercase">Hero filter — design lab · pick one</p>
            <nav class="flex gap-2">
                @foreach (['01 Console', '02 Manifest', '03 Signal', '04 Showroom'] as $i => $label)
                    <a href="#design-{{ $i + 1 }}"
                       class="border border-line bg-white px-3 py-1.5 font-mono text-[10px] tracking-widest uppercase transition hover:border-ink">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         DESIGN 1 — CONTROL CONSOLE
         Instrument-panel header strip, LIVE status, hard offset shadow,
         "armed" filter states, sliding-arrow CTA.
    ═════════════════════════════════════════════════════════════════ --}}
    <section id="design-1" class="scroll-mt-16">
        <div class="border-y border-line bg-paper px-4 py-3 sm:px-6">
            <p class="mx-auto max-w-7xl font-mono text-[11px] tracking-widest text-signal uppercase">
                01 — Control console <span class="text-ink/40">· header strip + live dot · block shadow · armed filter states · sliding arrow</span>
            </p>
        </div>

        <div class="relative overflow-hidden bg-ink-soft text-white">
            {{-- Spotlit blueprint grid --}}
            <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
                 style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px; mask-image: radial-gradient(700px 380px at 28% 35%, black, transparent 75%); -webkit-mask-image: radial-gradient(700px 380px at 28% 35%, black, transparent 75%);"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h1 class="text-2xl font-black tracking-tight uppercase sm:text-3xl" style="font-stretch: 115%;">
                        Find your car direct from Korea
                    </h1>
                    <p class="font-mono text-[11px] tracking-widest text-white/75 uppercase tabular-nums">
                        12,438 units in stock
                    </p>
                </div>

                <div class="mt-6 bg-white text-ink shadow-[10px_10px_0_rgba(0,0,0,0.85)]">
                    {{-- Console header strip --}}
                    <div class="flex items-center justify-between border-b border-line bg-paper px-4 py-2">
                        <p class="font-mono text-[10px] tracking-[0.25em] text-ink/55 uppercase">Stock filter — KR → Worldwide</p>
                        <p class="flex items-center gap-2 font-mono text-[10px] tracking-widest text-ink/55 uppercase">
                            <span class="pulse-dot inline-block size-2 rounded-full bg-signal"></span> Live feed
                        </p>
                    </div>

                    <div class="p-4">
                        <div class="relative">
                            <input type="search" placeholder="Search brand, model, or grade — e.g. Toyota, Roomy, GT" class="field !h-12 !pr-12">
                            <span class="pointer-events-none absolute top-1/2 right-3 grid -translate-y-1/2 place-items-center text-signal">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                            {{-- armed state: label goes signal + marker --}}
                            <div>
                                <label class="mb-1 flex items-center gap-1.5 font-mono text-[11px] tracking-wider text-signal uppercase">
                                    <span class="inline-block size-1.5 bg-signal"></span> Brands
                                </label>
                                <button type="button" class="field flex items-center justify-between gap-2 border-ink text-left">
                                    <span class="truncate font-semibold">Toyota, Kia</span>
                                    <span class="grid size-4 shrink-0 place-items-center bg-signal font-mono text-[9px] font-semibold text-white">2</span>
                                </button>
                            </div>
                            <div>
                                <label class="microlabel mb-1 block">Models</label>
                                <button type="button" class="field flex items-center justify-between gap-2 text-left text-ink/60">
                                    <span class="truncate">All models</span>
                                    <svg class="size-3.5 shrink-0 text-ink/40" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                                </button>
                            </div>
                            <div>
                                <label class="mb-1 flex items-center gap-1.5 font-mono text-[11px] tracking-wider text-signal uppercase">
                                    <span class="inline-block size-1.5 bg-signal"></span> Year
                                </label>
                                <button type="button" class="field flex items-center justify-between gap-2 border-ink text-left">
                                    <span class="truncate font-semibold">2019 — 2024</span>
                                </button>
                            </div>
                            <div>
                                <label class="microlabel mb-1 block">Fuel</label>
                                <button type="button" class="field flex items-center justify-between gap-2 text-left text-ink/60">
                                    <span class="truncate">All fuels</span>
                                    <svg class="size-3.5 shrink-0 text-ink/40" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-stretch justify-between gap-3 border-t border-line bg-paper px-4 py-3 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-5">
                            <button class="font-mono text-[11px] tracking-widest text-ink/60 uppercase transition hover:text-signal">+ More filters</button>
                            <button class="font-mono text-[11px] tracking-widest text-ink/40 uppercase transition hover:text-signal">Reset</button>
                        </div>
                        <button class="group flex items-center justify-center gap-3 bg-ink px-8 py-3 font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase transition hover:bg-signal">
                            Search 12,438 vehicles
                            <span class="transition-transform duration-200 group-hover:translate-x-1.5">→</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         DESIGN 2 — EXPORT MANIFEST
         Hazard strip, corner registration brackets, ghost typography,
         rubber stamp, barcode footer detail.
    ═════════════════════════════════════════════════════════════════ --}}
    <section id="design-2" class="scroll-mt-16">
        <div class="border-y border-line bg-paper px-4 py-3 sm:px-6">
            <p class="mx-auto max-w-7xl font-mono text-[11px] tracking-widest text-signal uppercase">
                02 — Export manifest <span class="text-ink/40">· hazard strip · corner brackets · ghost type · stamp + barcode</span>
            </p>
        </div>

        <div class="relative overflow-hidden bg-ink-soft text-white">
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]"
                 style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>
            {{-- Ghost typography --}}
            <span class="pointer-events-none absolute -right-8 -bottom-10 leading-none font-black tracking-tight uppercase select-none"
                  style="font-size: 13rem; color: transparent; -webkit-text-stroke: 2px rgba(255,255,255,0.07); font-stretch: 115%;">
                Korea
            </span>

            <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h1 class="text-2xl font-black tracking-tight uppercase sm:text-3xl" style="font-stretch: 115%;">
                        Find your car direct from Korea
                    </h1>
                    <p class="font-mono text-[11px] tracking-widest text-white/75 uppercase tabular-nums">
                        Manifest № 2026-07 · 12,438 units
                    </p>
                </div>

                <div class="relative mt-8">
                    {{-- Corner registration brackets --}}
                    <span class="pointer-events-none absolute -top-2 -left-2 z-10 size-6 border-t-2 border-l-2 border-signal"></span>
                    <span class="pointer-events-none absolute -top-2 -right-2 z-10 size-6 border-t-2 border-r-2 border-signal"></span>
                    <span class="pointer-events-none absolute -bottom-2 -left-2 z-10 size-6 border-b-2 border-l-2 border-signal"></span>
                    <span class="pointer-events-none absolute -right-2 -bottom-2 z-10 size-6 border-r-2 border-b-2 border-signal"></span>

                    {{-- Stamp --}}
                    <span class="pointer-events-none absolute -top-5 right-8 z-10 -rotate-6 border-2 border-signal px-3 py-1 font-mono text-[11px] font-semibold tracking-[0.3em] text-signal uppercase opacity-90"
                          style="background: rgba(255,255,255,0.92);">
                        FOB · Export ready
                    </span>

                    <div class="bg-white text-ink shadow-2xl">
                        <div class="hazard h-1.5"></div>

                        <div class="p-4">
                            <div class="relative">
                                <input type="search" placeholder="Search brand, model, or grade — e.g. Toyota, Roomy, GT" class="field !h-12 !pr-12">
                                <span class="pointer-events-none absolute top-1/2 right-3 grid -translate-y-1/2 place-items-center text-signal">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                                @foreach ([['Brands', 'All brands'], ['Models', 'All models'], ['Year', 'Any year'], ['Body type', 'All bodies']] as [$label, $ph])
                                    <div>
                                        <label class="microlabel mb-1 block">{{ $label }}</label>
                                        <button type="button" class="field flex items-center justify-between gap-2 text-left text-ink/60">
                                            <span class="truncate">{{ $ph }}</span>
                                            <svg class="size-3.5 shrink-0 text-ink/40" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-col items-stretch justify-between gap-3 border-t border-line bg-paper px-4 py-3 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-4">
                                {{-- Barcode detail --}}
                                <span class="inline-block h-6 w-24 opacity-70"
                                      style="background-image: repeating-linear-gradient(90deg, #1b1b1b 0 2px, transparent 2px 4px, #1b1b1b 4px 7px, transparent 7px 9px, #1b1b1b 9px 10px, transparent 10px 14px);"></span>
                                <span class="font-mono text-[10px] tracking-widest text-ink/45 uppercase">REF KR-2026-STOCK</span>
                            </div>
                            <div class="flex items-center gap-5">
                                <button class="font-mono text-[11px] tracking-widest text-ink/60 uppercase transition hover:text-signal">+ More filters</button>
                                <button class="bg-signal px-8 py-3 font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase transition hover:bg-ink">
                                    Search 12,438 vehicles
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         DESIGN 3 — SIGNAL BLOCK
         Full signal-red hero, underline-only fields, typography-first,
         black block shadow.
    ═════════════════════════════════════════════════════════════════ --}}
    <section id="design-3" class="scroll-mt-16">
        <div class="border-y border-line bg-paper px-4 py-3 sm:px-6">
            <p class="mx-auto max-w-7xl font-mono text-[11px] tracking-widest text-signal uppercase">
                03 — Signal block <span class="text-ink/40">· all-red hero · underline fields · typography-first · minimal</span>
            </p>
        </div>

        <div class="relative overflow-hidden bg-signal text-white">
            <div class="pointer-events-none absolute inset-0 opacity-[0.08]"
                 style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6">
                <p class="font-mono text-[11px] tracking-[0.3em] text-white/70 uppercase">Korea → Worldwide · FOB pricing</p>
                <h1 class="mt-2 max-w-3xl text-4xl leading-[0.95] font-black tracking-tight uppercase sm:text-5xl" style="font-stretch: 115%;">
                    Find your car<br>direct from Korea
                </h1>

                <div class="mt-8 bg-white p-6 text-ink shadow-[12px_12px_0_#1b1b1b] sm:p-8">
                    <div class="relative">
                        <input type="search" placeholder="Toyota, Roomy, GT…"
                               class="h-14 w-full border-0 border-b-2 border-ink/20 bg-transparent pr-12 text-xl font-bold tracking-tight uppercase transition-colors outline-none placeholder:font-normal placeholder:text-ink/30 focus:border-signal">
                        <span class="pointer-events-none absolute top-1/2 right-1 grid -translate-y-1/2 place-items-center text-signal">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        </span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-x-8 gap-y-5 lg:grid-cols-4">
                        @foreach ([['Brands', 'All brands'], ['Models', 'All models'], ['Year', 'Any year'], ['Mileage', 'Any mileage']] as [$label, $ph])
                            <div>
                                <label class="microlabel mb-1 block">{{ $label }}</label>
                                <button type="button" class="flex h-10 w-full items-center justify-between gap-2 border-0 border-b border-ink/20 bg-transparent text-left text-sm text-ink/60 transition-colors hover:border-ink">
                                    <span class="truncate">{{ $ph }}</span>
                                    <svg class="size-3.5 shrink-0 text-ink/40" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-col items-stretch justify-between gap-4 sm:flex-row sm:items-center">
                        <p class="font-mono text-[11px] tracking-widest text-ink/45 uppercase tabular-nums">12,438 units match</p>
                        <div class="flex items-center gap-6">
                            <button class="font-mono text-[11px] tracking-widest text-ink/50 uppercase transition hover:text-signal">+ More filters</button>
                            <button class="group flex items-center gap-3 bg-ink px-10 py-3.5 font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase transition hover:bg-signal">
                                Search <span class="transition-transform duration-200 group-hover:translate-x-1.5">→</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════════
         DESIGN 4 — NIGHT SHOWROOM
         Dark luxe gradient + signal glow, frosted glass panel,
         dark fields, soft elegance.
    ═════════════════════════════════════════════════════════════════ --}}
    <section id="design-4" class="scroll-mt-16">
        <div class="border-y border-line bg-paper px-4 py-3 sm:px-6">
            <p class="mx-auto max-w-7xl font-mono text-[11px] tracking-widest text-signal uppercase">
                04 — Night showroom <span class="text-ink/40">· dark luxe · signal glow · frosted glass panel · dark fields</span>
            </p>
        </div>

        <div class="relative overflow-hidden text-white" style="background: linear-gradient(160deg, #232323 0%, #101010 60%, #1a0d0d 100%);">
            {{-- Signal glow --}}
            <div class="pointer-events-none absolute inset-0"
                 style="background: radial-gradient(560px 320px at 78% 20%, rgba(223,54,54,0.22), transparent 70%), radial-gradient(400px 260px at 10% 90%, rgba(223,54,54,0.10), transparent 70%);"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.04]"
                 style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 56px 56px;"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="font-mono text-[11px] tracking-[0.3em] text-signal uppercase">Autocraft Korea</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight uppercase sm:text-3xl" style="font-stretch: 115%;">
                            Find your car direct from Korea
                        </h1>
                    </div>
                    <p class="font-mono text-[11px] tracking-widest text-white/50 uppercase tabular-nums">12,438 units in stock</p>
                </div>

                <div class="mt-7 border border-white/10 bg-white/[0.06] shadow-2xl backdrop-blur-xl">
                    <div class="p-5">
                        <div class="relative">
                            <input type="search" placeholder="Search brand, model, or grade — e.g. Toyota, Roomy, GT"
                                   class="h-12 w-full border border-white/15 bg-white/[0.07] px-3.5 pr-12 text-sm text-white transition-colors outline-none placeholder:text-white/35 focus:border-signal">
                            <span class="pointer-events-none absolute top-1/2 right-3 grid -translate-y-1/2 place-items-center text-signal">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                            @foreach ([['Brands', 'All brands'], ['Models', 'All models'], ['Year', 'Any year'], ['Transmission', 'Any gearbox']] as [$label, $ph])
                                <div>
                                    <label class="mb-1 block font-mono text-[11px] tracking-wider text-white/45 uppercase">{{ $label }}</label>
                                    <button type="button" class="flex h-10 w-full items-center justify-between gap-2 border border-white/15 bg-white/[0.07] px-3 text-left text-sm text-white/60 transition-colors hover:border-white/40">
                                        <span class="truncate">{{ $ph }}</span>
                                        <svg class="size-3.5 shrink-0 text-white/35" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col items-stretch justify-between gap-3 border-t border-white/10 px-5 py-3.5 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-5">
                            <button class="font-mono text-[11px] tracking-widest text-white/50 uppercase transition hover:text-signal">+ More filters</button>
                            <button class="font-mono text-[11px] tracking-widest text-white/35 uppercase transition hover:text-signal">Reset</button>
                        </div>
                        <button class="bg-signal px-8 py-3 font-mono text-xs font-semibold tracking-[0.2em] text-white uppercase shadow-[0_0_28px_rgba(223,54,54,0.45)] transition hover:bg-white hover:text-ink">
                            Search 12,438 vehicles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <p class="font-mono text-[11px] tracking-widest text-ink/45 uppercase">
            Tell Claude which design number to apply — the real filter keeps all its Livewire behavior; only the cosmetics change.
        </p>
    </footer>

</body>
</html>
