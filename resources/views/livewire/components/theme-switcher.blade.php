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

<div x-data="{
    mode: (function () {
        var stored = null;
        try { stored = localStorage.getItem('theme'); } catch (e) {}
        return stored || document.documentElement.getAttribute('data-theme') || 'system';
    })(),
    set(m) {
        if (this.mode === m) { return; }
        this.mode = m;
        try { localStorage.setItem('theme', m); } catch (e) {}
        $dispatch('theme-changed', { mode: m });
        $wire.setMode(m);
    },
}" @theme-changed.window="mode = $event.detail.mode"
    class="flex items-center gap-0.5 rounded-lg border border-zinc-200 bg-white p-0.5 dark:border-zinc-700 dark:bg-zinc-900"
    role="group" aria-label="Colour theme">
    <button type="button" @click="set('light')" :aria-pressed="mode === 'light'"
        :title="mode === 'light' ? 'Light theme' : 'Switch to light theme'" aria-label="Light theme"
        :class="mode === 'light'
            ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300'
            : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'"
        class="flex h-7 w-7 items-center justify-center rounded-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
        <x-icon name="sun" class="h-4 w-4" />
    </button>
    <button type="button" @click="set('dark')" :aria-pressed="mode === 'dark'"
        :title="mode === 'dark' ? 'Dark theme' : 'Switch to dark theme'" aria-label="Dark theme"
        :class="mode === 'dark'
            ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300'
            : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'"
        class="flex h-7 w-7 items-center justify-center rounded-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
        <x-icon name="moon" class="h-4 w-4" />
    </button>
    <button type="button" @click="set('system')" :aria-pressed="mode === 'system'"
        :title="mode === 'system' ? 'Follows your device setting' : 'Follow device setting'" aria-label="System theme"
        :class="mode === 'system'
            ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300'
            : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100'"
        class="flex h-7 w-7 items-center justify-center rounded-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
        <x-icon name="globe" class="h-4 w-4" />
    </button>
</div>