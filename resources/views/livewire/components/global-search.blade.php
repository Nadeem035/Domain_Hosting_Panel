<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public string $query = '';

    public bool $open = false;

    public array $results = ['clients' => [], 'services' => []];

    #[On('global-search-cleared')]
    public function clear(): void
    {
        $this->query = '';
        $this->results = ['clients' => [], 'services' => []];
        $this->open = false;
    }

    public function updatedQuery(string $value): void
    {
        $this->open = true;

        $value = trim($value);

        if (mb_strlen($value) < 2) {
            $this->results = ['clients' => [], 'services' => []];

            return;
        }

        $this->results = [
            'clients' => \App\Models\Client::query()
                ->where('name', 'like', "%{$value}%")
                ->orWhere('email', 'like', "%{$value}%")
                ->orWhere('company', 'like', "%{$value}%")
                ->limit(5)
                ->get()
                ->map(fn ($client) => [
                    'label' => $client->name,
                    'sub' => $client->company ?? $client->email,
                    'url' => route('clients.show', $client),
                ])
                ->all(),
            'services' => \App\Models\Service::query()
                ->with(['client:id,name'])
                ->where('domain_name', 'like', "%{$value}%")
                ->limit(5)
                ->get()
                ->map(fn ($service) => [
                    'label' => $service->domain_name ?? $service->type->label(),
                    'sub' => $service->client?->name,
                    'url' => route('services.show', $service),
                ])
                ->all(),
        ];
    }
};
?>

<div x-data="{ open: $wire.entangle('open'), hasQuery: false }"
    x-init="hasQuery = @js($query) !== ''"
    @click.outside="open = false; $wire.clear()"
    class="relative w-full max-w-md flex-1 sm:flex-none">
    <div class="relative">
        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
        <input
            type="search"
            wire:model.live.debounce.250ms="query"
            x-model:input="hasQuery = true"
            placeholder="Search clients or domains…"
            class="input !rounded-full !pl-9 !pr-3 sm:w-72"
            autocomplete="off"
        />
    </div>

    <div x-show="open" x-transition.opacity x-transition.scale.origin.top
        class="absolute left-0 right-0 top-12 z-50 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-card dark:border-zinc-700 dark:bg-zinc-900">
        <div x-show="hasQuery && !@js(count($results['clients']) + count($results['services']))" class="px-4 py-6 text-center">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">No results for "<span x-text="$wire.query"></span>"</p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-600">Try a client name, email or domain.</p>
        </div>

        <template x-if="hasQuery">
            <div>
                @if (count($results['clients']))
                    <p class="px-4 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Clients</p>
                    @foreach ($results['clients'] as $client)
                        <a href="{{ $client['url'] }}" wire:navigate @click="open = false; hasQuery = false; $wire.clear()"
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                                {{ strtoupper(substr($client['label'], 0, 1)) }}
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $client['label'] }}</span>
                                <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $client['sub'] }}</span>
                            </span>
                        </a>
                    @endforeach
                @endif

                @if (count($results['services']))
                    <p class="px-4 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Services</p>
                    @foreach ($results['services'] as $service)
                        <a href="{{ $service['url'] }}" wire:navigate @click="open = false; hasQuery = false; $wire.clear()"
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <x-icon name="globe" class="h-4 w-4 shrink-0 text-primary-500" />
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $service['label'] }}</span>
                                <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $service['sub'] }}</span>
                            </span>
                        </a>
                    @endforeach
                @endif
            </div>
        </template>
    </div>
</div>