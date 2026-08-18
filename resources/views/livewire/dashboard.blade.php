<div>
    <x-page-heading title="Dashboard" subtitle="Your reseller business at a glance." />

    {{-- Stat cards --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                <x-icon name="services" class="h-4 w-4" />
                Active services
            </div>
            <p class="mt-3 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->stats['active_services']) }}</p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">not cancelled</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                <x-icon name="clients" class="h-4 w-4" />
                Total clients
            </div>
            <p class="mt-3 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->stats['total_clients']) }}</p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">in your portfolio</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                <x-icon name="currency-dollar" class="h-4 w-4" />
                Revenue this month
            </div>
            <p class="mt-3 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ number_format($this->stats['monthly_revenue'], 2) }} <span class="text-lg text-zinc-400">{{ auth()->user()->defaultCurrency() }}</span>
            </p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">paid renewals</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                <x-icon name="clock" class="h-4 w-4" />
                Renewals due
            </div>
            <p class="mt-3 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($this->stats['upcoming_renewals']) }}</p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">within 30 days</p>
        </div>
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
                init() {
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
            }" class="mt-4 h-64">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Needs attention</h2>
                <a href="{{ route('reports.index') }}" wire:navigate
                    class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    Reports
                    <x-icon name="arrow-up-right" class="h-3.5 w-3.5" />
                </a>
            </div>
            <div class="mt-4 space-y-1">
                @forelse ($this->expiringSoon as $service)
                    @php($tier = $service->expiry_date ? \App\Services\ReminderTierCalculator::tierFor($service->expiry_date) : null)
                    <a href="{{ route('services.show', $service) }}" wire:navigate
                        class="group flex items-center gap-3 rounded-lg px-2 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-sm font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ strtoupper(substr($service->domain_name ?: $service->hostingPlan?->name ?: 'S', 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-zinc-800 group-hover:text-primary-600 dark:text-zinc-100 dark:group-hover:text-primary-400">
                                {{ $service->domain_name ?: $service->hostingPlan?->name ?: 'Service #'.$service->id }}
                            </span>
                            <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $service->client?->name ?? 'No client' }} · {{ $service->expiry_date?->format('M j, Y') }}
                            </span>
                        </span>
                        @if ($tier)
                            <span class="badge {{ match ($tier) {
                                $tier::Expired => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400',
                                $tier::Urgent => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                                $tier::DueSoon => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                                default => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400',
                            } }}">
                                {{ $tier->label() }}
                            </span>
                        @endif
                    </a>
                @empty
                    <div class="py-10 text-center">
                        <x-icon name="check-circle" class="mx-auto h-8 w-8 text-emerald-500" />
                        <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-300">All caught up</p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">No tracked services expire in the next 30 days.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>