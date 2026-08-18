<div>
    <x-page-heading title="Panels & Plans" subtitle="The servers, control panels, and hosting plans behind your services.">
        <x-slot:actions>
            <a href="{{ route('panels.create') }}" wire:navigate class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Add panel
            </a>
        </x-slot:actions>
    </x-page-heading>

    {{-- Filters --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative w-full sm:max-w-xs">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name, host, IP…"
                class="input !pl-9">
        </div>
        <select wire:model.live="typeFilter" class="input sm:w-44">
            <option value="">All types</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
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

        <div wire:loading.remove wire:target="search, typeFilter, sortBy, goToPage, previousPage, nextPage">
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
            <x-data-table :columns="[
                ['key' => 'name', 'label' => 'Panel', 'sortable' => true],
                ['key' => 'host', 'label' => 'Host', 'sortable' => true],
                ['key' => 'type', 'label' => 'Type', 'sortable' => true],
                ['key' => 'hosting_plans_count', 'label' => 'Plans', 'sortable' => true, 'class' => 'text-right'],
                ['key' => 'services_count', 'label' => 'Active services', 'sortable' => true, 'class' => 'text-right'],
                ['label' => '', 'class' => 'text-right'],
            ]" :rows="$this->panels" :sort-by="$this->sortBy" :sort-dir="$this->sortDir">
                @forelse ($this->panels as $panel)
                    <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                        <td class="px-5 py-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                                    {{ strtoupper(substr($panel->name, 0, 1)) }}
                                </span>
                                <span class="min-w-0">
                                    <a href="{{ route('panels.show', $panel) }}" wire:navigate class="block truncate font-semibold text-zinc-900 hover:text-primary-600 dark:text-zinc-100 dark:hover:text-primary-400">
                                        {{ $panel->name }}
                                        @unless ($panel->is_active)
                                            <span class="badge ml-1 bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400">Inactive</span>
                                        @endunless
                                    </a>
                                    <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $panel->username ? '@'.$panel->username : '—' }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="max-w-[200px] px-5 py-4 text-zinc-600 dark:text-zinc-300">
                            <span class="block truncate">{{ $panel->host ?? '—' }}</span>
                            <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $panel->ip_address ?? '' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $panel->type->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right text-zinc-600 dark:text-zinc-300">
                            {{ $panel->hosting_plans_count }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $panel->services_count }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1" @click.stop>
                                <a href="{{ route('panels.show', $panel) }}" wire:navigate class="btn-ghost !p-2" title="View">
                                    <x-icon name="eye" class="h-4 w-4" />
                                </a>
                                <a href="{{ route('panels.edit', $panel) }}" wire:navigate class="btn-ghost !p-2" title="Edit">
                                    <x-icon name="pencil-square" class="h-4 w-4" />
                                </a>
                                <button wire:click="confirmDelete({{ $panel->id }})" class="btn-ghost !p-2 text-rose-500 hover:!bg-rose-50 hover:!text-rose-600 dark:hover:!bg-rose-500/10" title="Delete">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            @if ($this->search !== '' || $this->typeFilter !== '')
                                <x-empty-state icon="search" title="No panels match your filters">
                                    <button wire:click="$set('search', ''); $set('typeFilter', '')" class="btn-ghost text-primary-600 dark:text-primary-400">
                                        Clear filters
                                    </button>
                                </x-empty-state>
                            @else
                                <x-empty-state icon="panels" title="No panels yet" text="Add the servers and control panels you use so you can attach plans and services.">
                                    <a href="{{ route('panels.create') }}" wire:navigate class="btn-primary">
                                        <x-icon name="plus" class="h-4 w-4" />
                                        Add your first panel
                                    </a>
                                </x-empty-state>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
                </div>

                @if ($this->panels->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-700/60">{{ $this->panels->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Delete confirmation --}}
    <x-modal wire:model="deleting" title="Delete panel">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deleting?->name }}</span>?
            Its plans stay on file but lose their panel link, and existing services are unaffected.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('deleting', null)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="delete" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete panel
            </button>
        </div>
    </x-modal>
</div>