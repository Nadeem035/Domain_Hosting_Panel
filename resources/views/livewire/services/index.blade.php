<div>
    <x-page-heading title="Services" subtitle="Domains and hosting plans you resell, with expiry tracking and renewals.">
        <x-slot:actions>
            <a href="{{ route('services.create') }}" wire:navigate class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Add service
            </a>
        </x-slot:actions>
    </x-page-heading>

    {{-- Filters --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap">
        <div class="relative w-full sm:max-w-xs sm:flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search domain or client…"
                class="input !pl-9">
        </div>
        <select wire:model.live="typeFilter" class="input sm:w-44">
            <option value="">All types</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" class="input sm:w-44">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
        <select wire:model.live="panelFilter" class="input sm:w-48">
            <option value="">All panels</option>
            @foreach ($panels as $panel)
                <option value="{{ $panel->id }}">{{ $panel->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2 sm:ml-auto">
            <button wire:click="exportCsv" class="btn-secondary !px-3 text-xs" title="Export CSV">
                <x-icon name="arrow-down-tray" class="h-4 w-4" />
                CSV
            </button>
            <button wire:click="exportExcel" class="btn-secondary !px-3 text-xs" title="Export Excel">
                <x-icon name="arrow-down-tray" class="h-4 w-4" />
                Excel
            </button>
            <button wire:click="exportPdf" class="btn-secondary !px-3 text-xs" title="Export PDF">
                <x-icon name="arrow-down-tray" class="h-4 w-4" />
                PDF
            </button>
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

        <div wire:loading.remove wire:target="search, typeFilter, statusFilter, panelFilter, sortBy, goToPage, previousPage, nextPage">
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
            <x-data-table :columns="[
                ['key' => 'domain_name', 'label' => 'Service', 'sortable' => true],
                ['label' => 'Client'],
                ['key' => 'type', 'label' => 'Type', 'sortable' => true],
                ['key' => 'expiry_date', 'label' => 'Expiry', 'sortable' => true],
                ['key' => 'company_price', 'label' => 'Provider cost', 'sortable' => true, 'class' => 'text-right'],
                ['key' => 'client_price', 'label' => 'Client price', 'sortable' => true, 'class' => 'text-right'],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['label' => '', 'class' => 'text-right'],
            ]" :rows="$this->services" :sort-by="$this->sortBy" :sort-dir="$this->sortDir">
                @forelse ($this->services as $service)
                    @php($tier = $tierFor($service->expiry_date))
                    <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                        <td class="px-5 py-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                                    {{ strtoupper(substr($service->domain_name ?: $service->hostingPlan?->name ?: 'S', 0, 1)) }}
                                </span>
                                <span class="min-w-0">
                                    <a href="{{ route('services.show', $service) }}" wire:navigate class="block truncate font-semibold text-zinc-900 hover:text-primary-600 dark:text-zinc-100 dark:hover:text-primary-400">
                                        {{ $service->domain_name ?: $service->hostingPlan?->name ?: 'Service #'.$service->id }}
                                    </a>
                                    <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">
                                        {{ $service->panel?->name }}{{ $service->hostingPlan ? ' · '.$service->hostingPlan->name : '' }}
                                    </span>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                            <span class="block truncate">{{ $service->client?->name ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $service->type->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <span class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $service->expiry_date?->format('M j, Y') }}</span>
                            @if ($tier)
                                <span class="badge mt-0.5 {{ match ($tier) {
                                    $tier::Expired => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400',
                                    $tier::Urgent => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                                    $tier::DueSoon => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                                    default => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400',
                                } }}">
                                    {{ $tier->label() }}
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right text-zinc-600 dark:text-zinc-300">
                            {{ number_format((float) $service->company_price, 2) }} <span class="text-xs text-zinc-400">{{ $service->currency }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <span class="block font-medium text-zinc-800 dark:text-zinc-200">{{ number_format((float) $service->client_price, 2) }} <span class="text-xs text-zinc-400">{{ $service->currency }}</span></span>
                            @if ($service->profit() > 0)
                                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">+{{ number_format($service->profit(), 2) }}</span>
                            @elseif ($service->profit() < 0)
                                <span class="text-xs font-semibold text-rose-500">{{ number_format($service->profit(), 2) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge {{ match ($service->status) {
                                $service->status::Active => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                                $service->status::Expired => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400',
                                $service->status::PendingRenewal => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                                default => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300',
                            } }}">
                                {{ $service->status->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1" @click.stop>
                                <button wire:click="confirmRenew({{ $service->id }})" class="btn-ghost !p-2 text-primary-600 hover:!bg-primary-50 dark:text-primary-400 dark:hover:!bg-primary-500/10" title="Renew">
                                    <x-icon name="arrow-path" class="h-4 w-4" />
                                </button>
                                <a href="{{ route('services.show', $service) }}" wire:navigate class="btn-ghost !p-2" title="View">
                                    <x-icon name="eye" class="h-4 w-4" />
                                </a>
                                <a href="{{ route('services.edit', $service) }}" wire:navigate class="btn-ghost !p-2" title="Edit">
                                    <x-icon name="pencil-square" class="h-4 w-4" />
                                </a>
                                <button wire:click="confirmDelete({{ $service->id }})" class="btn-ghost !p-2 text-rose-500 hover:!bg-rose-50 hover:!text-rose-600 dark:hover:!bg-rose-500/10" title="Delete">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            @if ($this->search !== '' || $this->typeFilter !== '' || $this->statusFilter !== '' || $this->panelFilter !== '')
                                <x-empty-state icon="search" title="No services match your filters">
                                    <button wire:click="$set('search', ''); $set('typeFilter', ''); $set('statusFilter', ''); $set('panelFilter', '')" class="btn-ghost text-primary-600 dark:text-primary-400">
                                        Clear filters
                                    </button>
                                </x-empty-state>
                            @else
                                <x-empty-state icon="services" title="No services yet" text="Add the domains and hosting plans you resell to start tracking renewals and reminders.">
                                    <a href="{{ route('services.create') }}" wire:navigate class="btn-primary">
                                        <x-icon name="plus" class="h-4 w-4" />
                                        Add your first service
                                    </a>
                                </x-empty-state>
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

    {{-- Renew modal --}}
    <x-modal wire:model="renewing" title="Renew service">
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Renewing <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $renewing?->domain_name ?: $renewing?->hostingPlan?->name ?: 'this service' }}</span>
                for <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $renewing?->hostingPlan?->billing_cycle?->label() ?: '1 month' }}</span>.
                @if ($renewing)
                    Expiry moves from <span class="font-medium">{{ $renewing->expiry_date?->format('M j, Y') }}</span> to
                    <span class="font-medium">{{ $renewing->expiry_date?->copy()->addMonths($renewing->hostingPlan?->billing_cycle?->months() ?? 1)->format('M j, Y') }}</span>.
                @endif
            </p>
            <label class="flex cursor-pointer items-center gap-3">
                <input type="checkbox" wire:model="renewPaymentReceived" class="checkbox">
                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Payment received</span>
            </label>
            @if ($renewPaymentReceived)
                <div>
                    <x-input-label for="renewInvoiceNumber" value="Invoice number" />
                    <x-text-input id="renewInvoiceNumber" wire:model="renewInvoiceNumber" class="mt-1.5 w-full" placeholder="INV-1024" />
                </div>
            @endif
            <div>
                <x-input-label for="renewNotes" value="Notes" />
                <textarea id="renewNotes" wire:model="renewNotes" rows="2" class="input mt-1.5" placeholder="Anything worth remembering…"></textarea>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('renewing', null)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="renew" class="btn-primary">
                <x-icon name="arrow-path" class="h-4 w-4" />
                Renew service
            </button>
        </div>
    </x-modal>

    {{-- Delete confirmation --}}
    <x-modal wire:model="deleting" title="Delete service">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deleting?->domain_name ?: $deleting?->hostingPlan?->name ?: 'this service' }}</span>?
            Its renewal history is deleted along with it.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('deleting', null)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="delete" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete service
            </button>
        </div>
    </x-modal>
</div>