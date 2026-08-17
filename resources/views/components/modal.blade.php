@props(['title' => null, 'maxWidth' => 'md'])

@php
    $widths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ];
@endphp

<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-init="$watch('show', value => document.body.classList.toggle('overflow-y-hidden', Boolean(value)))"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
    role="dialog" aria-modal="true">
    <div x-show="show" x-transition.opacity @click="show = false"
        class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm"></div>

    <div x-show="show" x-transition.scale.origin.bottom
        class="card relative w-full !rounded-2xl p-6 shadow-card-hover sm:p-8 {{ $widths[$maxWidth] }}">
        @if ($title)
            <div class="flex items-start justify-between gap-4">
                <h3 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
                <button type="button" @click="show = false"
                    class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    aria-label="Close">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>
        @endif
        <div class="{{ $title ? 'mt-4' : '' }}">{{ $slot }}</div>
    </div>
</div>