<div>
    <x-page-heading title="Clients" subtitle="Everyone you sell domains and hosting to.">
        <x-slot:actions>
            <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Add client
            </a>
        </x-slot:actions>
    </x-page-heading>

    {{-- Filters --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative w-full sm:max-w-xs">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name, email, company…"
                class="input !pl-9">
        </div>
        <select wire:model.live="statusFilter" class="input sm:w-44">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="mt-4">
        <div wire:loading.delay.long class="space-y-3">
            @for ($i = 0; $i < 4; $i++)
                <div class="card flex items-center gap-4 p-4">
                    <div class="skeleton h-10 w-10 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton h-4 w-40"></div>
                        <div class="skeleton h-3 w-64"></div>
                    </div>
                </div>
            @endfor
        </div>

        <div wire:loading.remove wire:target="search, statusFilter, goToPage, previousPage, nextPage">
            <div class="card overflow-hidden">
                <div class="grid grid-cols-1 gap-x-6 gap-y-1 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 sm:grid-cols-[2fr_1.5fr_1fr_1fr_auto]">
                    <span>Client</span>
                    <span>Contact</span>
                    <span>Active services</span>
                    <span>Monthly revenue</span>
                    <span></span>
                </div>

                @forelse ($this->clients as $client)
                    <a href="{{ route('clients.show', $client) }}" wire:navigate
                        class="table-row grid grid-cols-1 gap-2 px-5 py-4 transition hover:bg-zinc-50/80 sm:grid-cols-[2fr_1.5fr_1fr_1fr_auto] sm:items-center dark:hover:bg-zinc-800/40">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $client->name }}</span>
                                <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $client->company ?? '—' }}</span>
                            </span>
                        </div>
                        <div class="min-w-0 text-sm text-zinc-600 dark:text-zinc-300">
                            <span class="block truncate">{{ $client->email ?? '—' }}</span>
                            <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $client->phone ?? '' }}</span>
                        </div>
                        <div>
                            <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $client->active_services_count }}
                            </span>
                            <span class="badge ml-1 {{ $client->status === \App\Enums\ClientStatus::Active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ $client->status->label() }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ number_format((float) ($client->active_revenue ?? 0), 2) }} {{ auth()->user()->defaultCurrency() }}
                        </div>
                        <div class="flex items-center gap-1" @click.stop>
                            <a href="{{ route('clients.show', $client) }}" wire:navigate class="btn-ghost !p-2" title="View">
                                <x-icon name="eye" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('clients.edit', $client) }}" wire:navigate class="btn-ghost !p-2" title="Edit">
                                <x-icon name="pencil-square" class="h-4 w-4" />
                            </a>
                            <button wire:click="confirmDelete({{ $client->id }})" class="btn-ghost !p-2 text-rose-500 hover:!bg-rose-50 hover:!text-rose-600 dark:hover:!bg-rose-500/10" title="Delete">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-16 text-center">
                        @if ($this->search !== '' || $this->statusFilter !== '')
                            <x-icon name="search" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">No clients match your filters</p>
                            <button wire:click="$set('search', ''); $set('statusFilter', '')" class="btn-ghost mt-2 text-primary-600 dark:text-primary-400">
                                Clear filters
                            </button>
                        @else
                            <x-icon name="clients" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">No clients yet</p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Add your first client to start tracking their domains and hosting.</p>
                            <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary mt-4">
                                <x-icon name="plus" class="h-4 w-4" />
                                Add your first client
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            @if ($this->clients->hasPages())
                <div class="mt-4">{{ $this->clients->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Delete confirmation --}}
    <x-modal wire:model="deleting" title="Delete client">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deleting?->name }}</span>?
            Their service and renewal history will be kept (soft delete), but the client will no longer appear in lists.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('deleting', null)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="delete" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete client
            </button>
        </div>
    </x-modal>
</div>