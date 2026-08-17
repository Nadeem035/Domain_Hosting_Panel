<div>
    <x-page-heading title="Users" subtitle="Accounts that log in to the panel — admins manage everyone.">
        <x-slot:actions>
            <a href="{{ route('users.create') }}" wire:navigate class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Add user
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
        <select wire:model.live="roleFilter" class="input sm:w-44">
            <option value="">All roles</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
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

        <div wire:loading.remove wire:target="search, roleFilter, sortBy, goToPage, previousPage, nextPage">
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <x-data-table :columns="[
                            ['key' => 'name', 'label' => 'User', 'sortable' => true],
                            ['key' => 'company_name', 'label' => 'Company', 'sortable' => true],
                            ['key' => 'timezone', 'label' => 'Timezone', 'sortable' => true],
                            ['label' => 'Role'],
                            ['key' => 'clients_count', 'label' => 'Clients', 'sortable' => true, 'class' => 'text-right'],
                            ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
                            ['label' => '', 'class' => 'text-right'],
                        ]" :sort-by="$this->sortBy" :sort-dir="$this->sortDir" />
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($this->users as $user)
                                <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                    <td class="px-5 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">
                                                    {{ $user->name }}
                                                    @if ($user->id === auth()->id())
                                                        <span class="text-xs font-normal text-zinc-400">(you)</span>
                                                    @endif
                                                </span>
                                                <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $user->email }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="max-w-[200px] px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        <span class="block truncate">{{ $user->company_name ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $user->timezone }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($user->roles as $role)
                                                <span class="badge {{ $role->name === 'admin' ? 'bg-primary-100 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @empty
                                                <span class="badge bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">None</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right text-zinc-600 dark:text-zinc-300">{{ $user->clients_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $user->created_at?->format('M j, Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('users.edit', $user) }}" wire:navigate class="btn-ghost !p-2" title="Edit">
                                                <x-icon name="pencil-square" class="h-4 w-4" />
                                            </a>
                                            @if ($user->id !== auth()->id())
                                                <button wire:click="confirmDelete({{ $user->id }})" class="btn-ghost !p-2 text-rose-500 hover:!bg-rose-50 hover:!text-rose-600 dark:hover:!bg-rose-500/10" title="Delete">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        @if ($this->search !== '' || $this->roleFilter !== '')
                                            <x-empty-state icon="search" title="No users match your filters">
                                                <button wire:click="$set('search', ''); $set('roleFilter', '')" class="btn-ghost text-primary-600 dark:text-primary-400">
                                                    Clear filters
                                                </button>
                                            </x-empty-state>
                                        @else
                                            <x-empty-state icon="users" title="No users yet" text="Create the first account to let someone log in.">
                                                <a href="{{ route('users.create') }}" wire:navigate class="btn-primary">
                                                    <x-icon name="plus" class="h-4 w-4" />
                                                    Add your first user
                                                </a>
                                            </x-empty-state>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($this->users->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-700/60">{{ $this->users->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Delete confirmation --}}
    <x-modal wire:model="deleting" title="Delete user">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deleting?->name }}</span>?
            Their clients and services will stay in the database but will no longer be manageable.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('deleting', null)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="delete" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete user
            </button>
        </div>
    </x-modal>
</div>