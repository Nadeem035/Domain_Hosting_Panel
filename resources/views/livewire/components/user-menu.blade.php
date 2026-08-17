<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
};
?>

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button @click="open = !open"
        class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-bold text-white ring-2 ring-white transition hover:ring-primary-200 dark:ring-zinc-900 dark:hover:ring-primary-800"
        aria-label="Account menu">
        {{ strtoupper(substr(auth()->user()?->name ?? '?', 0, 1)) }}
    </button>

    <div x-show="open" x-transition.opacity x-transition.scale.origin.top.right
        class="absolute right-0 top-12 z-50 w-64 rounded-xl border border-zinc-200 bg-white p-1.5 shadow-card dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-100 px-3 py-2.5 dark:border-zinc-800">
            <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()?->name }}</p>
            <p class="truncate text-xs text-zinc-500">{{ auth()->user()?->email }}</p>
        </div>
        <div class="pt-1.5">
            <a href="{{ route('profile') }}" wire:navigate @click="open = false"
                class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <x-icon name="user-circle" class="h-5 w-5 text-zinc-400" />
                Profile
            </a>
            <a href="{{ route('settings.index') }}" wire:navigate @click="open = false"
                class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <x-icon name="settings" class="h-5 w-5 text-zinc-400" />
                Settings
            </a>
            <button wire:click="logout" @click="open = false"
                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                <x-icon name="logout" class="h-5 w-5" />
                Log out
            </button>
        </div>
    </div>
</div>