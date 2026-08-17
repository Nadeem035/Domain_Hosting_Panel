<?php

use Livewire\Volt\Component;

new class extends Component
{
    public function setMode(string $mode): void
    {
        if (! in_array($mode, ['light', 'dark', 'system'], true) || ! auth()->check()) {
            return;
        }

        auth()->user()->update(['theme_preference' => $mode]);
    }
};
?>

<div x-data="{ dark: document.documentElement.classList.contains('dark') }"
    @theme-changed.window="dark = $event.detail.mode === 'dark'">
    <button @click="
        dark = !dark;
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        $wire.setMode(dark ? 'dark' : 'light');
        $dispatch('theme-changed', { mode: dark ? 'dark' : 'light' });
    "
        class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
        :title="dark ? 'Switch to light mode' : 'Switch to dark mode'"
        aria-label="Toggle theme">
        <x-icon name="sun" class="hidden h-5 w-5 dark:block" />
        <x-icon name="moon" class="h-5 w-5 dark:hidden" />
    </button>
</div>