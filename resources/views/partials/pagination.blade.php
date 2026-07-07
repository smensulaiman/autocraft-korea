@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max(1, min($current - 2, $last - 4));
        $end = min($last, max($current + 2, 5));
    @endphp

    <nav class="flex flex-wrap items-center justify-between gap-4" role="navigation" aria-label="Pagination">
        <p class="microlabel">
            Showing {{ number_format($paginator->firstItem() ?? 0) }}–{{ number_format($paginator->lastItem() ?? 0) }}
            of {{ number_format($paginator->total()) }}
        </p>

        <div class="flex flex-wrap items-center gap-1 font-mono text-xs">
            <button wire:click="previousPage" @disabled($paginator->onFirstPage()) aria-label="Previous page"
                    class="grid h-9 min-w-9 place-items-center border border-line bg-white transition hover:border-ink disabled:cursor-not-allowed disabled:opacity-30">
                ←
            </button>

            @if ($start > 1)
                <button wire:click="gotoPage(1)"
                        class="grid h-9 min-w-9 place-items-center border border-line bg-white px-2 transition hover:border-ink">1</button>
                @if ($start > 2)
                    <span class="px-1 text-ink/40">…</span>
                @endif
            @endif

            @foreach (range($start, $end) as $page)
                @if ($page === $current)
                    <span class="grid h-9 min-w-9 place-items-center border border-ink bg-ink px-2 font-semibold text-white">{{ number_format($page) }}</span>
                @else
                    <button wire:click="gotoPage({{ $page }})"
                            class="grid h-9 min-w-9 place-items-center border border-line bg-white px-2 transition hover:border-ink">{{ number_format($page) }}</button>
                @endif
            @endforeach

            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="px-1 text-ink/40">…</span>
                @endif
                <button wire:click="gotoPage({{ $last }})"
                        class="grid h-9 min-w-9 place-items-center border border-line bg-white px-2 transition hover:border-ink">{{ number_format($last) }}</button>
            @endif

            <button wire:click="nextPage" @disabled(! $paginator->hasMorePages()) aria-label="Next page"
                    class="grid h-9 min-w-9 place-items-center border border-line bg-white transition hover:border-ink disabled:cursor-not-allowed disabled:opacity-30">
                →
            </button>
        </div>
    </nav>
@endif
