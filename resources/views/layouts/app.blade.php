<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'AutoCraft — Korea Direct Stock' }}</title>

    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">

    <header class="bg-ink text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6">
            <a href="{{ route('vehicles.index') }}" class="group flex items-center gap-3">
                <span class="grid size-9 place-items-center bg-signal font-mono text-sm font-semibold text-white">AC</span>
                <span class="leading-none">
                    <span class="block text-lg font-extrabold tracking-tight uppercase" style="font-stretch: 115%;">AutoCraft</span>
                    <span class="mt-1 block font-mono text-[10px] tracking-[0.25em] text-white/50 uppercase">Korea Direct Stock</span>
                </span>
            </a>

            <nav class="flex items-center gap-6">
                <div x-data="{ time: '--:--:--' }"
                     x-init="const tick = () => time = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Tokyo', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).format(new Date()); tick(); setInterval(tick, 1000)"
                     class="hidden items-center gap-2 font-mono text-[11px] tracking-widest text-white/60 uppercase tabular-nums sm:flex">
                    JST <span x-text="time"></span>
                </div>
                <a href="{{ route('vehicles.index') }}"
                   class="font-mono text-xs tracking-widest text-white/70 uppercase transition hover:text-signal">
                    Stock
                </a>
            </nav>
        </div>
        <div class="h-0.5 bg-signal"></div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Verified memberships --}}
    <section class="mt-16 border-t border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            <div class="mb-6 flex items-end gap-4">
                <div class="shrink-0">
                    <p class="microlabel">// Trusted export credentials</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight uppercase">Verified member of</h2>
                </div>
                <div class="mb-1.5 hidden h-px flex-1 bg-line sm:block"></div>
                <a href="https://www.autocraftjapan.com/pages/about" target="_blank" rel="noopener"
                   class="mb-0.5 shrink-0 bg-ink px-5 py-2.5 font-mono text-[11px] tracking-widest text-white uppercase transition hover:bg-signal">
                    More about us
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ([
                    ['url' => 'https://www.pekema.org.my/', 'image' => '/images/members/pekema_member.webp', 'name' => 'PEKEMA', 'detail' => 'Malaysia Association of Malay Vehicle Importers & Traders'],
                    ['url' => 'https://www.tokyo-cci.or.jp/english/', 'image' => '/images/members/tokyo_chamber_member.webp', 'name' => 'Tokyo Chamber of Commerce & Industry', 'detail' => 'Member C-1851438'],
                    ['url' => 'https://www.jastpro.org/eng/', 'image' => '/images/members/jastpro_logo_home.webp', 'name' => 'JASTPRO', 'detail' => 'Code P0049756000'],
                    ['url' => 'https://www.jumvea.or.jp/', 'image' => '/images/members/jumvea_member.webp', 'name' => 'JUMVEA', 'detail' => 'Japan Used Motor Vehicle Exporters Association (420)'],
                ] as $member)
                    <a href="{{ $member['url'] }}" target="_blank" rel="noopener"
                       class="group flex flex-col border border-line bg-white p-4 transition hover:border-ink">
                        <div class="grid h-16 place-items-center">
                            <img src="{{ $member['image'] }}" alt="{{ $member['name'] }}" loading="lazy"
                                 class="max-h-14 w-auto max-w-full object-contain">
                        </div>
                        <p class="mt-3 text-[12.5px] font-bold tracking-tight uppercase">{{ $member['name'] }}</p>
                        <p class="mt-0.5 font-mono text-[10px] leading-relaxed tracking-wide text-ink/50 uppercase">{{ $member['detail'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="bg-ink text-white">
        <div class="h-0.5 bg-signal"></div>
        <div class="mx-auto max-w-7xl px-4 py-8 text-center sm:px-6">
            <p class="font-mono text-[11px] tracking-widest text-white/50 uppercase">
                Developed by Sendajapan
            </p>
        </div>
    </footer>

</body>
</html>
