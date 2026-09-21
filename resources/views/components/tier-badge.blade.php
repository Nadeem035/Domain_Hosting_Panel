@props(['tier', 'label' => null])

@php
    $value = $tier instanceof \BackedEnum ? $tier->value : $tier;
    $labelText = $label ?? ($tier instanceof \BackedEnum ? $tier->label() : match ($value) {
        'urgent' => 'Urgent',
        'due_soon' => 'Due soon',
        'upcoming' => 'Upcoming',
        'expired' => 'Expired',
        'none' => 'Tracked',
        default => '—',
    });

    $styles = match ($value) {
        'expired' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'urgent' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
        'due_soon' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'upcoming' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        default => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $styles]) }}>
    {{ $labelText }}
</span>
