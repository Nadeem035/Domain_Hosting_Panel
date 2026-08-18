<div>
    <x-page-heading title="Invoices" subtitle="Service renewal payment records." />

    {{-- Filters --}}
    <div class="mt-4 mb-4">
        <div class="flex flex-wrap gap-2">
            <select wire:model.live="statusFilter" class="input">
                <option value="all">All</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>

            <div class="relative w-full lg:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search invoice number or service…"
                    class="input !pl-9">
            </div>
        </div>
    </div>

    {{-- Table --}}
    @if ($invoices->count() > 0)
        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700/60">
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Renewed</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Client</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Service / Domain</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Tier</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Provider cost</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Client price</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Invoice #</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Paid on</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Status</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-200 dark:bg-zinc-950">
                    @forelse ($invoices as $invoice)
                        <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500 dark:text-zinc-400">
                                {{ $invoice->renewed_on ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500 dark:text-zinc-400">
                                {{ $invoice->service?->client?->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @if ($invoice->service)
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $invoice->service->domain_name ?: '—' }}</span>
                                    @if ($invoice->service->hostingPlan)
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500"> · {{ $invoice->service->hostingPlan->name }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $tier = $invoice->service ? \App\Services\ReminderTierCalculator::tierFor($invoice->service->expiry_date) : null;
                                @endphp
                                @if ($tier)
                                    <span class="badge {{ match ($tier) {
                                        $tier::Expired => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400',
                                        $tier::Urgent => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                                        $tier::DueSoon => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                                        default => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400',
                                    } }}">
                                        {{ $tier->label() }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-right text-zinc-600 dark:text-zinc-300">
                                {{ number_format((float) $invoice->client_price, 2) }} {{ $invoice->currency ?: auth()->user()->defaultCurrency() ?? 'USD' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $invoice->invoice_number ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500 dark:text-zinc-400">
                                {{ $invoice->payment_received_date ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @if ($invoice->payment_received)
                                    <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Paid</span>
                                @else
                                    <span class="badge bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400">Unpaid</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @if ($invoice->payment_received)
                                    Paid
                                @else
                                    <button wire:click="$set('selectedId', {{$invoice->id}})" class="btn-ghost text-xs text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:bg-zinc-800/60" title="Mark as paid">
                                        <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                        Mark as paid
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-zinc-400 dark:text-zinc-600">
                                <x-icon name="check-circle" class="mx-auto mb-3 h-8 w-8 text-emerald-500" />
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">No renewals found</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Try adjusting the filters or search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Export + summary --}}
        <div class="mt-6 flex justify-between items-center">
            <button wire:click="exportCsv" class="btn-secondary">
                <x-icon name="arrow-down-tray" class="h-4 w-4" /> Export CSV
            </button>

            @if ($invoices->count() > 0)
                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $invoices->count() }} renewal{{ $invoices->count() > 1 ? 's' : '' }} displayed
                </div>
                <button wire:click="markPaid"
                    class="btn-ghost text-xs ml-2 {{ $selectedId > 0 ? '' : 'opacity-50 cursor-not-allowed' }}"
                    title="Mark selected renewal as paid">
                    <x-icon name="check-circle" class="h-3.5 w-3.5" /> Mark as paid
                </button>
            @endif
        </div>
    @else
        <div class="py-12 text-center text-zinc-500 dark:text-zinc-400">
            <x-icon name="check-circle" class="mx-auto mb-3 h-12 w-12 text-emerald-500" />
            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">No renewals found</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-400">Adjust the filters above to find the invoices you're looking for.</p>
        </div>
    @endif
</div>