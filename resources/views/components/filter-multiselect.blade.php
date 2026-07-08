@props([
    'label',
    'model',
    'options' => [],
    'selected' => [],
    'placeholder' => 'All',
    'disabled' => false,
    'disabledText' => null,
])

@php $armed = ! $disabled && ! empty($selected); @endphp

<div x-data="{ open: false }" x-on:click.outside="open = false" class="relative">
    <label class="microlabel mb-1 flex items-center gap-1.5 {{ $armed ? '!text-signal' : '' }}">
        @if ($armed)
            <span class="inline-block size-1.5 bg-signal"></span>
        @endif
        {{ $label }}
    </label>
    <button type="button" x-on:click="open = !open" @disabled($disabled)
            class="field flex items-center justify-between gap-2 text-left disabled:cursor-not-allowed disabled:opacity-50 {{ $armed ? '!border-ink font-semibold' : '' }}">
        <span class="truncate">
            @if ($disabled)
                {{ $disabledText ?? $placeholder }}
            @elseif (empty($selected))
                {{ $placeholder }}
            @else
                {{ count($selected) }} selected
            @endif
        </span>
        @if ($armed)
            <span class="grid size-4 shrink-0 place-items-center bg-signal font-mono text-[9px] font-semibold text-white">{{ count($selected) }}</span>
        @else
            <svg class="size-3.5 shrink-0 text-ink/40" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5"/></svg>
        @endif
    </button>
    <div x-show="open" x-cloak x-transition.opacity.duration.150ms
         class="absolute z-40 mt-1 max-h-96 w-full min-w-56 overflow-y-auto border border-line bg-white shadow-xl">
        @forelse ($options as $value => $display)
            <label class="flex cursor-pointer items-center gap-2.5 px-3 py-2 transition hover:bg-paper">
                <input type="checkbox" wire:model.live="{{ $model }}" value="{{ $value }}" class="accent-signal">
                <span class="flex-1 truncate text-sm">{{ $display }}</span>
            </label>
        @empty
            <p class="px-3 py-3 text-sm text-ink/50">No options available.</p>
        @endforelse
    </div>
</div>
