<div>
    @php($tier = $this->tier)
    <x-page-heading :title="$service->domain_name ?: $service->hostingPlan?->name ?: 'Service #' . $service->id"
        :subtitle="$service->client?->name . ' · ' . $service->type->label()">
        <x-slot:actions>
            <a href="{{ route('services.edit', $service) }}" wire:navigate class="btn-secondary">
                <x-icon name="pencil-square" class="h-4 w-4" />
                Edit
            </a>
            <button wire:click="confirmingDelete = true" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete
            </button>
        </x-slot:actions>
    </x-page-heading>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_2fr]">
        {{-- Service details --}}
        <div class="card space-y-4 p-6">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-xl font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                    {{ strtoupper(substr($service->domain_name ?: $service->hostingPlan?->name ?: 'S', 0, 1)) }}
                </span>
                <div class="space-y-1">
                    <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $service->type->label() }}</span>
                    <span class="badge block {{ match ($service->status) {
                        $service->status::Active => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                        $service->status::Expired => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400',
                        $service->status::PendingRenewal => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                        default => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300',
                    } }}">
                        {{ $service->status->label() }}
                    </span>
                </div>
            </div>

            <dl class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <x-icon name="user-circle" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Client</dt>
                        <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                            <a href="{{ route('clients.show', $service->client) }}" wire:navigate class="hover:text-primary-600">{{ $service->client?->name }}</a>
                        </dd>
                    </div>
                </div>
                @if ($service->domain_name)
                    <div class="flex items-start gap-3">
                        <x-icon name="globe" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Domain</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $service->domain_name }}</dd>
                        </div>
                    </div>
                @endif
                @if ($service->panel)
                    <div class="flex items-start gap-3">
                        <x-icon name="server" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Panel</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                                <a href="{{ route('panels.show', $service->panel) }}" wire:navigate class="hover:text-primary-600">{{ $service->panel->name }}</a>
                            </dd>
                        </div>
                    </div>
                @endif
                @if ($service->hostingPlan)
                    <div class="flex items-start gap-3">
                        <x-icon name="circle-stack" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Plan</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $service->hostingPlan->name }}</dd>
                            <dd class="text-xs text-zinc-400">{{ $service->hostingPlan->billing_cycle->label() }}</dd>
                        </div>
                    </div>
                @endif
                <div class="flex items-start gap-3">
                    <x-icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Dates</dt>
                        <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                            {{ $service->created_date?->format('M j, Y') }} → {{ $service->expiry_date?->format('M j, Y') }}
                        </dd>
                        <dd class="text-xs text-zinc-400">
                            @if ($tier)
                                {{ $this->daysLeft >= 0 ? $this->daysLeft.' days left' : abs($this->daysLeft).' days overdue' }} · {{ $tier->label() }}
                            @else
                                More than 30 days out
                            @endif
                        </dd>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <x-icon name="currency-dollar" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Pricing</dt>
                        <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                            {{ number_format((float) $service->client_price, 2) }} {{ $service->currency }}
                            <span class="text-xs font-normal text-zinc-400">(cost {{ number_format((float) $service->company_price, 2) }})</span>
                        </dd>
                    </div>
                </div>
                @if ($service->auto_renew_tracking === false)
                    <div class="flex items-start gap-3">
                        <x-icon name="power" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Auto-renew tracking</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">Disabled</dd>
                        </div>
                    </div>
                @endif
            </dl>

            @if ($service->notes)
                <div class="rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Notes</p>
                    <p class="mt-1.5 whitespace-pre-wrap">{{ $service->notes }}</p>
                </div>
            @endif

            <div class="border-t border-zinc-100 pt-4 dark:border-zinc-800">
                <button wire:click="renew" class="btn-primary w-full">
                    <x-icon name="arrow-path" class="h-4 w-4" />
                    Renew now
                </button>
            </div>
        </div>

        {{-- Renewal history --}}
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Renewal history</h2>
                <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $service->renewals()->count() }}</span>
            </div>

            <div class="grid grid-cols-1 gap-x-4 gap-y-1 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 sm:grid-cols-[1fr_1.5fr_1fr_1fr_1fr]">
                <span>Renewed</span>
                <span>Expiry change</span>
                <span>Client price</span>
                <span>Payment</span>
                <span>Invoice</span>
            </div>

            @forelse ($this->renewals as $renewal)
                <div class="table-row grid grid-cols-1 gap-2 px-5 py-4 sm:grid-cols-[1fr_1.5fr_1fr_1fr_1fr] sm:items-center">
                    <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $renewal->renewed_on?->format('M j, Y') }}</div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-300">
                        {{ $renewal->previous_expiry_date?->format('M j, Y') }}
                        <x-icon name="arrow-right" class="mx-1 inline h-3.5 w-3.5 text-zinc-400" />
                        {{ $renewal->new_expiry_date?->format('M j, Y') }}
                    </div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-300">{{ number_format((float) $renewal->client_price, 2) }} {{ $service->currency }}</div>
                    <div>
                        <span class="badge {{ $renewal->payment_received ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' }}">
                            {{ $renewal->payment_received ? 'Paid' : 'Unpaid' }}
                        </span>
                    </div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-300">{{ $renewal->invoice_number ?? '—' }}</div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <x-icon name="arrow-path" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">No renewals yet</p>
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Renewing this service records its history here.</p>
                </div>
            @endforelse

            @if ($this->renewals->hasPages())
                <div class="px-5 pb-4">{{ $this->renewals->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Delete confirmation --}}
    <x-modal wire:model="confirmingDelete" title="Delete service">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $service->domain_name ?: $service->hostingPlan?->name ?: 'this service' }}</span>?
            Its renewal history is deleted along with it.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="delete" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete service
            </button>
        </div>
    </x-modal>
</div>