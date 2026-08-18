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
            'upcoming_renewals' => $liveServices()
                ->where('auto_renew_tracking', true)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
                ->count(),
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
     * The most urgent tracked services, for the "needs attention" list.
     */
    #[Computed]
    public function expiringSoon(): Collection
    {
        return Service::query()
            ->where('status', '!=', ServiceStatus::Cancelled->value)
            ->where('auto_renew_tracking', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->with(['client:id,name', 'hostingPlan:id,name'])
            ->orderBy('expiry_date')
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}