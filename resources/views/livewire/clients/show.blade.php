<div>
    <x-page-heading :title="$client->name"
        :subtitle="$client->company ? $client->company . ' — client since ' . $client->created_at?->format('M Y') : 'Client since ' . $client->created_at?->format('M Y')">
        <x-slot:actions>
            <a href="{{ route('clients.edit', $client) }}" wire:navigate class="btn-secondary">
                <x-icon name="pencil-square" class="h-4 w-4" />
                Edit
            </a>
            <button wire:click="confirmDelete" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete
            </button>
        </x-slot:actions>
    </x-page-heading>

    {{-- Contact card --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_2fr]">
        <div class="card space-y-4 p-6">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-xl font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                    {{ strtoupper(substr($client->name, 0, 1)) }}
                </span>
                <div>
                    <span class="badge {{ $client->status === \App\Enums\ClientStatus::Active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                        {{ $client->status->label() }}
                    </span>
                </div>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <x-icon name="user-circle" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Name</dt>
                        <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $client->name }}</dd>
                    </div>
                </div>
                @if ($client->email)
                    <div class="flex items-start gap-3">
                        <x-icon name="link" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Email</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                                <a href="mailto:{{ $client->email }}" class="hover:text-primary-600">{{ $client->email }}</a>
                            </dd>
                        </div>
                    </div>
                @endif
                @if ($client->phone)
                    <div class="flex items-start gap-3">
                        <x-icon name="building-office" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Phone</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $client->phone }}</dd>
                        </div>
                    </div>
                @endif
                @if ($client->address)
                    <div class="flex items-start gap-3">
                        <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Address</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $client->address }}</dd>
                        </div>
                    </div>
                @endif
            </dl>
            @if ($client->notes)
                <div class="rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Notes</p>
                    <p class="mt-1.5 whitespace-pre-wrap">{{ $client->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="card p-5">
                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Active services</p>
                    <p class="mt-1.5 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['active_count'] }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Total services</p>
                    <p class="mt-1.5 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['total_count'] }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Monthly revenue</p>
                    <p class="mt-1.5 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ number_format($this->stats['monthly_revenue'], 2) }}
                    </p>
                </div>
                <div class="card p-5">
                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Next expiry</p>
                    <p class="mt-1.5 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $this->stats['next_expiry']?->format('M j, Y') ?? '—' }}
                    </p>
                </div>
            </div>

            {{-- Services --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Services</h2>
                    <a href="{{ route('services.create', ['client' => $client->id]) }}" wire:navigate class="btn-ghost !px-2 text-primary-600 dark:text-primary-400">
                        <x-icon name="plus" class="h-4 w-4" />
                        Add service
                    </a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                    @forelse ($this->services as $service)
                        <a href="{{ route('services.show', $service['id']) }}" wire:navigate
                            class="flex flex-wrap items-center gap-3 px-5 py-4 transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                <x-icon :name="$service['type']->involvesDomain() ? 'globe' : 'server'" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $service['domain_name'] ?? $service['type']->label() }}
                                </span>
                                <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">
                                    {{ $service['panel_name'] ?? '—' }}{{ $service['plan_name'] ? ' · ' . $service['plan_name'] : '' }}
                                </span>
                            </span>
                            <span class="text-right">
                                <span class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ number_format((float) $service['client_price'], 2) }} {{ $service['currency'] }}
                                </span>
                                <span class="block text-xs text-zinc-400 dark:text-zinc-500">
                                    Expires {{ $service['expiry_date']->format('M j, Y') }}
                                </span>
                            </span>
                            <x-tier-badge :tier="$service['tier']"
                                :label="$service['tier'] ? $service['days_left'] . 'd · ' . $service['tier']->label() : $service['status']->label()" />
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <x-icon name="services" class="mx-auto h-9 w-9 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-300">No services yet</p>
                            <a href="{{ route('services.create', ['client' => $client->id]) }}" wire:navigate class="btn-primary mt-3">
                                <x-icon name="plus" class="h-4 w-4" />
                                Add the first service
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent activity --}}
            <div class="card overflow-hidden">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent activity</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                    @forelse ($this->recentActivity as $activity)
                        <div class="flex items-start gap-3 px-5 py-3.5">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <x-icon :name="$activity->event === 'deleted' ? 'trash' : 'pencil-square'" class="h-3.5 w-3.5" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm text-zinc-700 dark:text-zinc-300">
                                    <span class="font-medium">{{ $activity->description }}</span>
                                </p>
                                <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">No activity recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Delete confirmation --}}
    <x-modal wire:model="deleting" title="Delete client">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $client->name }}</span>?
            Their services and renewal history will be kept (soft delete), but the client will no longer appear in lists.
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