<div>
    <x-page-heading title="Dashboard" subtitle="Your reseller business at a glance." />

    @php($renewals = $this->renewalSnapshot)
    @php($renewalsTotal = array_sum($renewals))
    @php($hasExpired = $renewals['expired'] > 0)
    @php($hasUrgent = $renewals['urgent'] > 0)

    {{-- Stat cards --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                <x-icon name="services" class="h-4 w-4" />
                Active services
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-zinc-900 dark:text-zinc-100">{{ number_format($this->stats['active_services']) }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">not cancelled</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                <x-icon name="clients" class="h-4 w-4" />
                Total clients
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-zinc-900 dark:text-zinc-100">{{ number_format($this->stats['total_clients']) }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">in your portfolio</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                <x-icon name="currency-dollar" class="h-4 w-4" />
                Revenue this month
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-zinc-900 dark:text-zinc-100">
                {{ number_format($this->stats['monthly_revenue'], 2) }} <span class="text-lg text-zinc-500">{{ auth()->user()->defaultCurrency() }}</span>
            </p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">paid renewals</p>
        </div>
        <a href="{{ route('services.index') }}" wire:navigate
            class="card group p-5 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 {{ $hasExpired ? 'ring-1 ring-rose-300/70 dark:ring-rose-500/40' : ($hasUrgent ? 'ring-1 ring-amber-300/70 dark:ring-amber-500/40' : 'hover:border-zinc-300 dark:hover:border-zinc-700') }}">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider {{ $hasExpired ? 'text-rose-600 dark:text-rose-400' : ($hasUrgent ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500 dark:text-zinc-400') }}">
                <x-icon name="clock" class="h-4 w-4" />
                Renewals due
                <x-icon name="arrow-up-right" class="ml-auto h-4 w-4 opacity-0 transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:opacity-100" />
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums {{ $hasExpired ? 'text-rose-600 dark:text-rose-400' : ($hasUrgent ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-zinc-100') }}">
                {{ number_format($renewalsTotal) }}
            </p>
            <p class="mt-1 text-xs {{ $hasExpired ? 'text-rose-600 dark:text-rose-400' : ($hasUrgent ? 'text-amber-700 dark:text-amber-400' : 'text-zinc-500 dark:text-zinc-400') }}">
                @if ($hasExpired)
                    {{ $renewals['expired'] }} expired · {{ $renewals['urgent'] }} urgent
                @elseif ($hasUrgent)
                    {{ $renewals['urgent'] }} urgent · {{ $renewals['due_soon'] }} due soon
                @else
                    no service expires in the next 30 days
                @endif
            </p>
        </a>
    </div>

    {{-- Chart + attention list --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Revenue — last 6 months</h2>
                <span class="badge bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                    {{ auth()->user()->defaultCurrency() }}
                </span>
            </div>
            <div wire:ignore x-data="{
                labels: @js($this->revenueSeries['labels']),
                values: @js($this->revenueSeries['values']),
                chart: null,
                render() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const grid = isDark ? 'rgba(161,161,170,0.12)' : 'rgba(24,24,27,0.06)';
                    const label = isDark ? '#a1a1aa' : '#71717a';
                    this.chart = new Chart(this.$refs.canvas, {
                        type: 'bar',
                        data: {
                            labels: this.labels,
                            datasets: [{
                                label: 'Collected revenue',
                                data: this.values,
                                backgroundColor: 'rgba(100,103,227,0.75)',
                                hoverBackgroundColor: '#4f46d8',
                                borderRadius: 6,
                                maxBarThickness: 42,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: label, font: { size: 11 } } },
                                y: { grid: { color: grid }, border: { display: false }, ticks: { color: label, font: { size: 11 } } },
                            },
                        },
                    });
                },
                refresh() {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                    this.render();
                },
                init() {
                    this.render();
                },
            }" @theme-changed.window="refresh()" class="mt-4 h-64">
                @php($seriesSummary = collect($this->revenueSeries['labels'])->zip($this->revenueSeries['values'])
                    ->map(fn ($pair) => $pair[0].': '.$pair[1])
                    ->implode(', '))
                <canvas x-ref="canvas"
                    aria-label="Revenue collected per month ({{ auth()->user()->defaultCurrency() }}) — {{ $seriesSummary }}."></canvas>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Needs attention</h2>
                    @if ($renewalsTotal > 0)
                        <span class="badge bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $renewalsTotal }}</span>
                    @endif
                </div>
                <a href="{{ route('services.index') }}" wire:navigate
                    class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    All services
                    <x-icon name="arrow-up-right" class="h-3.5 w-3.5" />
                </a>
            </div>

            @if ($renewalsTotal > 0)
                <div class="mt-4 flex flex-wrap items-center gap-1.5" role="group" aria-label="Filter renewals by urgency">
                    @foreach (['all', 'expired', 'urgent', 'due_soon', 'upcoming'] as $value)
                        @php($count = $value === 'all' ? $renewalsTotal : $renewals[$value])
                        <button type="button"
                            wire:click="$set('attentionFilter', '{{ $value }}')"
                            aria-current="{{ $this->attentionFilter === $value ? 'true' : 'false' }}"
                            class="rounded-full border px-2.5 py-1 text-xs font-medium transition
                                {{ $this->attentionFilter === $value
                                    ? 'border-transparent bg-primary-600 text-white dark:bg-primary-500/20 dark:text-primary-200'
                                    : 'border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            {{ $value === 'all'
                                ? 'All'
                                : \Illuminate\Support\Str::of($value)->replace('_', ' ')->ucfirst() }}
                            <span class="{{ $count > 0 ? '' : 'opacity-40' }}">({{ $count }})</span>
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 space-y-1">
                @forelse ($this->attentionList as $service)
                    @php($tier = $service->expiry_date ? \App\Services\ReminderTierCalculator::tierFor($service->expiry_date) : null)
                    @php($days = $service->expiry_date ? \App\Services\ReminderTierCalculator::daysLeft($service->expiry_date) : 0)
                    <a href="{{ route('services.show', $service) }}" wire:navigate wire:key="attention-{{ $service->id }}"
                        class="group flex items-center gap-3 rounded-lg px-2 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-sm font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ strtoupper(substr($service->domain_name ?: $service->hostingPlan?->name ?: 'S', 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-zinc-800 group-hover:text-primary-600 dark:text-zinc-100 dark:group-hover:text-primary-400">
                                {{ $service->domain_name ?: $service->hostingPlan?->name ?: 'Service #'.$service->id }}
                            </span>
                            <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400" title="{{ $service->expiry_date?->format('M j, Y') }}">
                                {{ $service->client?->name ?? 'No client' }} ·
                                @if ($days < 0)
                                    expired {{ abs($days) }} {{ abs($days) === 1 ? 'day' : 'days' }} ago
                                @elseif ($days === 0)
                                    expires today
                                @else
                                    expires in {{ $days }} {{ $days === 1 ? 'day' : 'days' }}
                                @endif
                            </span>
                        </span>
                        @if ($tier)
                            <span class="hidden text-right sm:block">
                                <span class="block text-sm font-semibold tabular-nums text-zinc-700 dark:text-zinc-200">
                                    {{ $service->currency }} {{ number_format((float) $service->client_price, 2) }}
                                </span>
                                <x-tier-badge :tier="$tier" class="mt-1" />
                            </span>
                        @endif
                    </a>
                @empty
                    @if ($renewalsTotal === 0)
                        <div class="py-10 text-center">
                            <x-icon name="check-circle" class="mx-auto h-8 w-8 text-emerald-500" />
                            <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-300">All caught up</p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">No tracked services expire in the next 30 days.</p>
                        </div>
                    @else
                        <div class="py-10 text-center">
                            <x-icon name="clock" class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-300">Nothing in this tier</p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Pick another filter to see your renewals.</p>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>
    </div>
</div>