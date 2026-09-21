<div>
    @php($tier = $this->tier)
    @php($profit = (float) $service->client_price - (float) $service->company_price)
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

    {{-- Key stats --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                Client price
                @if ($service->hostingPlan?->billing_cycle)
                    <span class="ml-1 font-normal normal-case tracking-normal text-zinc-400/80 dark:text-zinc-500">{{ $service->hostingPlan->billing_cycle->label() }}</span>
                @endif
            </p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-zinc-900 dark:text-zinc-100">
                {{ number_format((float) $service->client_price, 2) }} <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500">{{ $service->currency }}</span>
            </p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Profit</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums {{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                @if ($profit >= 0)+@endif{{ number_format($profit, 2) }} <span class="text-xs font-medium text-zinc-400 dark:text-zinc-500">{{ $service->currency }}</span>
            </p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">cost {{ number_format((float) $service->company_price, 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Expires</p>
            <p class="mt-1.5 text-lg font-bold tabular-nums text-zinc-900 dark:text-zinc-100">
                {{ $service->expiry_date?->format('M j, Y') ?? '—' }}
            </p>
            @if ($service->expiry_date)
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                    {{ $this->daysLeft >= 0 ? $this->daysLeft.' days left' : abs($this->daysLeft).' days overdue' }}
                </p>
            @endif
        </div>
        <div class="card p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Renewals</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-zinc-900 dark:text-zinc-100">{{ $renewals->total() }}</p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                @if ($renewals->total() > 0)
                    {{ $this->service->renewals()->where('payment_received', false)->count() }} unpaid
                @else
                    No renewals yet
                @endif
            </p>
        </div>
    </div>

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
                        @if ($tier)
                            <dd class="text-xs text-zinc-400">{{ $tier->label() }}</dd>
                        @endif
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <x-icon name="currency-dollar" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Pricing</dt>
                        <dd class="font-medium tabular-nums text-zinc-800 dark:text-zinc-200">
                            {{ number_format((float) $service->client_price, 2) }} {{ $service->currency }}
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
        <x-data-table count-label="renewal" :rows="$renewals" :columns="[
            ['key' => 'renewed_on', 'label' => 'Renewed'],
            ['key' => 'expiry', 'label' => 'Expiry change'],
            ['key' => 'client_price', 'label' => 'Client price', 'class' => 'text-right'],
            ['key' => 'payment', 'label' => 'Payment'],
            ['key' => 'invoice', 'label' => 'Invoice', 'class' => 'text-right'],
        ]">
            @forelse ($renewals as $renewal)
                <tr class="transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                    <td class="whitespace-nowrap px-5 py-4 font-medium text-zinc-800 dark:text-zinc-200">{{ $renewal->renewed_on?->format('M j, Y') }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                        {{ $renewal->previous_expiry_date?->format('M j, Y') }}
                        <x-icon name="arrow-right" class="mx-1 inline h-3.5 w-3.5 text-zinc-400" />
                        {{ $renewal->new_expiry_date?->format('M j, Y') }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-zinc-600 dark:text-zinc-300">
                        {{ number_format((float) $renewal->client_price, 2) }} <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $service->currency }}</span>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <span class="badge {{ $renewal->payment_received ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' }}">
                            {{ $renewal->payment_received ? 'Paid' : 'Unpaid' }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right tabular-nums text-zinc-600 dark:text-zinc-300">{{ $renewal->invoice_number ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-empty-state icon="arrow-path" title="No renewals yet" text="Renewing this service records its history here." />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
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