@props([
    'icon' => 'information-circle',
    'title' => 'No records',
    'text' => null,
])

<div class="px-6 py-16 text-center">
    <x-icon :name="$icon" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
    <p class="mt-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ $title }}</p>
    @if ($text)
        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>