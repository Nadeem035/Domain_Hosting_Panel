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
    <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative w-full lg:max-w-sm">
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name, email, company…"
                class="input !pl-10">
            @if ($this->search !== '')
                <button type="button" wire:click="$set('search', '')"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-full p-1 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-200">
                    <x-icon name="x-mark" class="h-3.5 w-3.5" />
                </button>
            @endif
        </div>

        <div class="inline-flex w-full items-center gap-1 overflow-x-auto rounded-xl border border-zinc-200 bg-white p-1 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:w-auto">
            <button type="button" wire:click="$set('statusFilter', '')"
                class="inline-flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3.5 py-1.5 text-sm font-medium transition sm:flex-none {{ $this->statusFilter === '' ? 'bg-primary-600 text-white shadow-sm' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' }}">
                All
                <span class="rounded-full px-1.5 py-0.5 text-[11px] font-semibold leading-none {{ $this->statusFilter === '' ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                    {{ $this->statusCounts['all'] }}
                </span>
            </button>
            @foreach ($statuses as $status)
                <button type="button" wire:click="$set('statusFilter', '{{ $status->value }}')"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg px-3.5 py-1.5 text-sm font-medium transition sm:flex-none {{ $this->statusFilter === $status->value ? 'bg-primary-600 text-white shadow-sm' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' }}">
                    {{ $status->label() }}
                    <span class="rounded-full px-1.5 py-0.5 text-[11px] font-semibold leading-none {{ $this->statusFilter === $status->value ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                        {{ $this->statusCounts[$status->value] }}
                    </span>
                </button>
            @endforeach
        </div>
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

        <div wire:loading.remove wire:target="search, statusFilter, sortBy, goToPage, previousPage, nextPage">
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3.5 dark:border-zinc-800">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->clients->total() }}</span>
                        client{{ $this->clients->total() === 1 ? '' : 's' }}
                    </p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">
                        Page {{ $this->clients->currentPage() }} of {{ $this->clients->lastPage() }}
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <x-data-table :columns="[
                            ['key' => 'name', 'label' => 'Client', 'sortable' => true],
                            ['key' => 'email', 'label' => 'Contact', 'sortable' => true],
                            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                            ['key' => 'active_services_count', 'label' => 'Active services', 'sortable' => true, 'class' => 'text-right'],
                            ['key' => 'active_revenue', 'label' => 'Monthly revenue', 'sortable' => true, 'class' => 'text-right'],
                            ['label' => '', 'class' => 'text-right'],
                        ]" :sort-by="$this->sortBy" :sort-dir="$this->sortDir" />
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($this->clients as $client)
                                @php($avatar = ['from-violet-500 to-fuchsia-600', 'from-sky-500 to-indigo-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600'][abs(crc32($client->name)) % 5])
                                <tr class="group transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                    <td class="px-5 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br {{ $avatar }} text-sm font-bold text-white shadow-sm">
                                                {{ strtoupper(substr($client->name, 0, 1)) }}
                                            </span>
                                            <span class="min-w-0">
                                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="block truncate font-semibold text-zinc-900 transition group-hover:text-primary-600 dark:text-zinc-100 dark:group-hover:text-primary-400">
                                                    {{ $client->name }}
                                                </a>
                                                <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $client->company ?? '—' }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="max-w-[220px] px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        <span class="block truncate">{{ $client->email ?? '—' }}</span>
                                        <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $client->phone ?? '' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($client->status === \App\Enums\ClientStatus::Active)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                {{ $client->status->label() }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                                {{ $client->status->label() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <span class="font-semibold tabular-nums text-zinc-700 dark:text-zinc-200">{{ $client->active_services_count }}</span>
                                        <span class="ml-1 text-xs text-zinc-400 dark:text-zinc-500">active</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <span class="font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
                                            {{ number_format((float) ($client->active_revenue ?? 0), 2) }}
                                        </span>
                                        <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500"> {{ auth()->user()->defaultCurrency() }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 transition lg:opacity-0 lg:group-hover:opacity-100" @click.stop>
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        @if ($this->search !== '' || $this->statusFilter !== '')
                                            <x-empty-state icon="search" title="No clients match your filters">
                                                <button wire:click="$set('search', ''); $set('statusFilter', '')" class="btn-ghost text-primary-600 dark:text-primary-400">
                                                    Clear filters
                                                </button>
                                            </x-empty-state>
                                        @else
                                            <x-empty-state icon="clients" title="No clients yet" text="Add your first client to start tracking their domains and hosting.">
                                                <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary">
                                                    <x-icon name="plus" class="h-4 w-4" />
                                                    Add your first client
                                                </a>
                                            </x-empty-state>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($this->clients->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-700/60">{{ $this->clients->links() }}</div>
                @endif
            </div>
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