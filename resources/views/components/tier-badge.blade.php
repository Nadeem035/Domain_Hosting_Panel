@props(['tier', 'label' => null])

@php
    $styles = match ($tier?->value ?? $tier) {
        'urgent' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'due_soon' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'upcoming' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        'expired' => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700/60 dark:text-zinc-400',
        default => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $styles]) }}>
    {{ $label ?? ($tier ? $tier->label() : '—') }}
</span>