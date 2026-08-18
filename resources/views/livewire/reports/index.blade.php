<div>
    <x-page-heading title="Reports" subtitle="Renewal outlook — what needs your attention in the next 30 days.">
        <x-slot:actions>
            <select wire:model.live="tierFilter" class="input sm:w-44">
                <option value="">All tiers</option>
                @foreach ($tiers as $tier)
                    <option value="{{ $tier->value }}">{{ $tier->label() }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-page-heading>

    {{-- Tier summary --}}
    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="card p-5">
            <x-tier-badge tier="expired" label="Expired" />
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $tierCounts['expired'] }}</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">past expiry date</p>
        </div>
        <div class="card p-5">
            <x-tier-badge tier="urgent" label="Urgent" />
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $tierCounts['urgent'] }}</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">within 7 days</p>
        </div>
        <div class="card p-5">
            <x-tier-badge tier="due_soon" label="Due soon" />
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $tierCounts['due_soon'] }}</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">within 15 days</p>
        </div>
        <div class="card p-5">
            <x-tier-badge tier="upcoming" label="Upcoming" />
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $tierCounts['upcoming'] }}</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">within 30 days</p>
        </div>
        <div class="card p-5">
            <x-tier-badge tier="none" label="Tracked" />
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ array_sum($tierCounts) }}</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">services in the reminder engine</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="mt-6">
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

        <div wire:loading.remove wire:target="tierFilter, sortBy, goToPage, previousPage, nextPage">
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
            <x-data-table :columns="[
                ['label' => 'Client'],
                ['key' => 'domain_name', 'label' => 'Domain', 'sortable' => true],
                ['key' => 'type', 'label' => 'Type', 'sortable' => true],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['key' => 'tier', 'label' => 'Tier', 'sortable' => true],
                ['key' => 'expiry_date', 'label' => 'Expires', 'sortable' => true],
                ['key' => 'client_price', 'label' => 'Price', 'sortable' => true, 'class' => 'text-right'],
            ]" :rows="$this->services" :sort-by="$this->sortBy" :sort-dir="$this->sortDir">
                @forelse ($this->services as $service)
                    <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                        <td class="max-w-[180px] px-5 py-4 text-zinc-600 dark:text-zinc-300">
                            @if ($service->client)
                                <a href="{{ route('clients.show', $service->client) }}" wire:navigate class="block truncate hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ $service->client->name }}
                                </a>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>
                        <td class="max-w-[220px] px-5 py-4">
                            <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $service->domain_name }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $service->type->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge
                                {{ $service->status === \App\Enums\ServiceStatus::Active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : '' }}
                                {{ $service->status === \App\Enums\ServiceStatus::PendingRenewal ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : '' }}
                                {{ $service->status === \App\Enums\ServiceStatus::Expired ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400' : '' }}">
                                {{ $service->status->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <x-tier-badge tier="{{ $service->expiry_date ? \App\Services\ReminderTierCalculator::tierFor($service->expiry_date)?->value : 'none' }}" />
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                            @if ($service->expiry_date)
                                <span class="block">{{ $service->expiry_date->format('M j, Y') }}</span>
                                <span class="block text-xs text-zinc-400 dark:text-zinc-500">
                                    {{ \App\Services\ReminderTierCalculator::daysLeft($service->expiry_date) >= 0
                                        ? \App\Services\ReminderTierCalculator::daysLeft($service->expiry_date).' days left'
                                        : abs(\App\Services\ReminderTierCalculator::daysLeft($service->expiry_date)).' days overdue' }}
                                </span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ number_format((float) ($service->client_price ?? 0), 2) }} {{ auth()->user()->defaultCurrency() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            @if ($this->tierFilter !== '')
                                <x-empty-state icon="search" title="No services in this tier">
                                    <button wire:click="$set('tierFilter', '')" class="btn-ghost text-primary-600 dark:text-primary-400">
                                        Clear filter
                                    </button>
                                </x-empty-state>
                            @else
                                <x-empty-state icon="reports" title="Nothing to renew" text="Services with auto-renew tracking on will show up here as their expiry approaches." />
                            @endif
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
                </div>

                @if ($this->services->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-700/60">{{ $this->services->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>