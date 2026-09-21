<?php

namespace App\Livewire;

use App\Enums\ServiceStatus;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceRenewal;
use App\Services\ReminderTierCalculator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    private const ATTENTION_WINDOW_DAYS = 30;

    /**
     * Active tier filter for the "needs attention" list.
     *
     * @var 'all'|'expired'|'urgent'|'due_soon'|'upcoming'
     */
    public string $attentionFilter = 'all';

    /**
     * Four headline stats shown as cards.
     *
     * @return array{active_services: int, total_clients: int, monthly_revenue: float, upcoming_renewals: int}
     */
    #[Computed]
    public function stats(): array
    {
        $liveServices = fn () => Service::query()->where('status', '!=', ServiceStatus::Cancelled->value);

        return [
            'active_services' => $liveServices()->count(),
            'total_clients' => Client::query()->count(),
            'monthly_revenue' => $this->revenueBetween(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ),
            'upcoming_renewals' => array_sum($this->renewalSnapshot()),
        ];
    }

    /**
     * Revenue (client price) collected for paid renewals between two dates.
     */
    private function revenueBetween(string $from, string $to): float
    {
        return (float) ServiceRenewal::query()
            ->where('payment_received', true)
            ->whereBetween('payment_received_date', [$from, $to])
            ->sum('client_price');
    }

    /**
     * Last six months of collected revenue, for the chart.
     *
     * @return array{labels: list<string>, values: list<float>}
     */
    #[Computed]
    public function revenueSeries(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $i) => now()->copy()->subMonthsNoOverflow($i)->startOfMonth());

        $rows = ServiceRenewal::query()
            ->where('payment_received', true)
            ->where('payment_received_date', '>=', $months->first()->toDateString())
            ->get(['payment_received_date', 'client_price'])
            ->groupBy(fn (ServiceRenewal $renewal) => $renewal->payment_received_date->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('client_price'));

        return [
            'labels' => $months->map(fn ($month) => $month->format('M'))->values()->all(),
            'values' => $months->map(fn ($month) => (float) ($rows[$month->format('Y-m')] ?? 0))->values()->all(),
        ];
    }

    /**
     * Tier breakdown of tracked services inside the 30-day attention window.
     *
     * Already-expired services within the window count too — they still need action.
     *
     * @return array{expired: int, urgent: int, due_soon: int, upcoming: int}
     */
    #[Computed]
    public function renewalSnapshot(): array
    {
        $window = self::ATTENTION_WINDOW_DAYS;

        $counts = ['expired' => 0, 'urgent' => 0, 'due_soon' => 0, 'upcoming' => 0];

        Service::query()
            ->where('status', '!=', ServiceStatus::Cancelled->value)
            ->where('auto_renew_tracking', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                now()->subDays($window)->toDateString(),
                now()->addDays($window)->toDateString(),
            ])
            ->pluck('expiry_date')
            ->each(function ($expiry) use (&$counts) {
                $tier = ReminderTierCalculator::tierFor($expiry);
                if ($tier !== null) {
                    $counts[$tier->value]++;
                }
            });

        return $counts;
    }

    /**
     * Tracked services in the 30-day attention window, sorted for triage:
     * expired first (most recently expired first), then by nearest expiry.
     * Respects the active tier filter and caps the list for scanning.
     *
     * @return Collection<int, Service>
     */
    #[Computed]
    public function attentionList(): Collection
    {
        $window = self::ATTENTION_WINDOW_DAYS;
        $filter = $this->attentionFilter;

        $services = Service::query()
            ->where('status', '!=', ServiceStatus::Cancelled->value)
            ->where('auto_renew_tracking', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                now()->subDays($window)->toDateString(),
                now()->addDays($window)->toDateString(),
            ])
            ->with(['client:id,name', 'hostingPlan:id,name'])
            ->get();

        return $services
            ->filter(fn (Service $service) => $filter === 'all'
                || ReminderTierCalculator::tierFor($service->expiry_date)?->value === $filter)
            ->sortBy(function (Service $service) {
                $days = ReminderTierCalculator::daysLeft($service->expiry_date);

                return $days <= 0 ? [0, -$days] : [1, $days];
            }, SORT_REGULAR)
            ->take(8)
            ->values();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
