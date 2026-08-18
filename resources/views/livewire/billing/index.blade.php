<div>
    <x-page-heading title="Invoices" subtitle="Service renewal payment records." />

    {{-- Filters --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap">
        <div class="relative w-full sm:max-w-xs sm:flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search invoice number…"
                class="input !pl-9">
        </div>
        <select wire:model.live="statusFilter" class="input sm:w-40">
            <option value="all">All</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
        </select>
        <button wire:click="exportCsv" class="btn-secondary !px-3 text-xs sm:ml-auto" title="Export CSV">
            <x-icon name="arrow-down-tray" class="h-4 w-4" />
            Export CSV
        </button>
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
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <x-data-table :columns="[
                            ['key' => 'renewed_on', 'label' => 'Renewed', 'sortable' => true],
                            ['label' => 'Client'],
                            ['label' => 'Service / Domain'],
                            ['label' => 'Tier'],
                            ['key' => 'company_price', 'label' => 'Provider cost', 'sortable' => true, 'class' => 'text-right'],
                            ['key' => 'client_price', 'label' => 'Client price', 'sortable' => true, 'class' => 'text-right'],
                            ['label' => 'Invoice #'],
                            ['label' => 'Paid on'],
                            ['label' => 'Status'],
                            ['label' => '', 'class' => 'text-right'],
                        ]" :sort-by="$this->sortBy" :sort-dir="$this->sortDir" />
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($invoices as $invoice)
                                @php($tier = $invoice->service ? \App\Services\ReminderTierCalculator::tierFor($invoice->service->expiry_date) : null)
                                <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $invoice->renewed_on?->format('M j, Y') ?? '—' }}
                                    </td>
                                    <td class="max-w-[180px] px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        @if ($invoice->service?->client)
                                            <a href="{{ route('clients.show', $invoice->service->client) }}" wire:navigate class="block truncate hover:text-primary-600 dark:hover:text-primary-400">
                                                {{ $invoice->service->client->name }}
                                            </a>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                        @endif
                                    </td>
                                    <td class="max-w-[220px] px-5 py-4">
                                        @if ($invoice->service)
                                            <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoice->service->domain_name ?: 'Service #'.$invoice->service->id }}</span>
                                            @if ($invoice->service->hostingPlan)
                                                <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $invoice->service->hostingPlan->name }}</span>
                                            @endif
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
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
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-zinc-600 dark:text-zinc-300">
                                        {{ number_format((float) ($invoice->company_price ?? 0), 2) }} {{ $invoice->service?->currency ?: $currency }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-zinc-800 dark:text-zinc-200">
                                        {{ number_format((float) $invoice->client_price, 2) }} {{ $invoice->service?->currency ?: $currency }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $invoice->invoice_number ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $invoice->payment_received_date?->format('M j, Y') ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($invoice->payment_received)
                                            <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Paid</span>
                                        @else
                                            <span class="badge bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        @unless ($invoice->payment_received)
                                            <button wire:click="$set('selectedId', {{ $invoice->id }}); markPaid()" class="btn-ghost !p-2 text-primary-600 hover:!bg-primary-50 dark:text-primary-400 dark:hover:!bg-primary-500/10" title="Mark as paid">
                                                <x-icon name="check-circle" class="h-4 w-4" />
                                            </button>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <x-empty-state icon="check-circle" title="No renewals found"
                                            text="Try adjusting the filters or search.">
                                            <button wire:click="$set('search', ''); $set('statusFilter', 'all')" class="btn-ghost text-primary-600 dark:text-primary-400">
                                                Clear filters
                                            </button>
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($invoices->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-700/60">{{ $invoices->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>