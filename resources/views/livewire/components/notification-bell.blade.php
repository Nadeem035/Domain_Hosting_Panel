<?php

use Livewire\Volt\Component;

new class extends Component
{
    public function notifications()
    {
        return auth()->user()?->notifications()->limit(10)->get() ?? collect();
    }

    public function unreadCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public function markAllRead(): void
    {
        auth()->user()?->unreadNotifications()->update(['read_at' => now()]);
    }
};
?>

<div x-data="{ open: false }" @click.outside="open = false" class="relative" wire:poll.keep-alive="60s">
    <button @click="open = !open; if (open) $wire.markAllRead()"
        class="relative rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
        aria-label="Notifications">
        <x-icon name="bell" class="h-5 w-5" />
        @if ($this->unreadCount() > 0)
            <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                {{ $this->unreadCount() > 9 ? '9+' : $this->unreadCount() }}
            </span>
        @endif
    </button>

    <div x-show="open" x-transition.opacity x-transition.scale.origin.top.right
        class="absolute right-0 top-12 z-50 w-80 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-card sm:w-96 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Reminders</p>
            @if ($this->unreadCount() > 0)
                <button wire:click="markAllRead" class="text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    Mark all as read
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($this->notifications() as $notification)
                <a href="{{ $notification->data['url'] ?? route('dashboard') }}" wire:navigate @click="open = false"
                    class="flex items-start gap-3 border-b border-zinc-50 px-4 py-3 transition last:border-0 hover:bg-zinc-50 dark:border-zinc-800/60 dark:hover:bg-zinc-800/60">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                        {{ ($notification->data['tier'] ?? '') === 'urgent' ? 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' }}">
                        <x-icon name="exclamation-triangle" class="h-4 w-4" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $notification->data['title'] ?? 'Reminder' }}</span>
                        <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">{{ $notification->data['message'] ?? '' }}</span>
                        <span class="mt-1 block text-[11px] text-zinc-400 dark:text-zinc-600">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                    @if ($notification->unread())
                        <span class="ml-auto mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary-500"></span>
                    @endif
                </a>
            @empty
                <div class="px-4 py-10 text-center">
                    <x-icon name="check-circle" class="mx-auto h-8 w-8 text-emerald-500" />
                    <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-300">All caught up</p>
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">New renewal reminders will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>