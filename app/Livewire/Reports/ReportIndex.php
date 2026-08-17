<?php

namespace App\Livewire\Reports;

use App\Enums\ReminderTier;
use App\Enums\ServiceStatus;
use App\Livewire\Concerns\WithSorting;
use App\Models\Service;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ReportIndex extends Component
{
    use WithPagination, WithSorting;

    #[Url(as: 'tier', history: true)]
    public string $tierFilter = '';

    public string $sortBy = 'name';

    public function mount(): void
    {
        $this->sortBy = 'expiry_date';
    }

    public function updatedTierFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function services()
    {
        $query = Service::query()
            ->where('status', '!=', ServiceStatus::Cancelled->value)
            ->where('auto_renew_tracking', true)
            ->with(['client:id,name']);

        if ($this->tierFilter !== '') {
            $query->whereRaw("{$this->tierCaseSql()} = ?", [$this->tierFilter]);
        }

        if ($this->sortBy === 'tier') {
            $dir = $this->sortDir === 'desc' ? 'desc' : 'asc';

            $query->orderByRaw("{$this->tierSeveritySql()} {$dir}")
                ->orderBy('expiry_date');
        } else {
            $this->applySorting($query, ['domain_name', 'type', 'status', 'expiry_date', 'client_price']);
        }

        return $query->paginate(12);
    }

    #[Computed]
    public function tierCounts(): array
    {
        $rows = Service::query()
            ->where('status', '!=', ServiceStatus::Cancelled->value)
            ->where('auto_renew_tracking', true)
            ->selectRaw("{$this->tierCaseSql()} as tier, COUNT(*) as total")
            ->groupBy('tier')
            ->pluck('total', 'tier');

        return [
            'expired' => (int) ($rows['expired'] ?? 0),
            'urgent' => (int) ($rows['urgent'] ?? 0),
            'due_soon' => (int) ($rows['due_soon'] ?? 0),
            'upcoming' => (int) ($rows['upcoming'] ?? 0),
            'none' => (int) ($rows['none'] ?? 0),
        ];
    }

    public function render()
    {
        return view('livewire.reports.index', [
            'services' => $this->services,
            'tierCounts' => $this->tierCounts,
            'tiers' => ReminderTier::cases(),
        ]);
    }

    private function tierBounds(): array
    {
        $today = now()->startOfDay();

        return [
            $today->toDateString(),
            $today->copy()->addDays(7)->toDateString(),
            $today->copy()->addDays(15)->toDateString(),
            $today->copy()->addDays(30)->toDateString(),
        ];
    }

    private function tierCaseSql(): string
    {
        [$expired, $urgent, $due, $upcoming] = $this->tierBounds();

        return "CASE WHEN expiry_date < '{$expired}' THEN 'expired' "
            ."WHEN expiry_date <= '{$urgent}' THEN 'urgent' "
            ."WHEN expiry_date <= '{$due}' THEN 'due_soon' "
            ."WHEN expiry_date <= '{$upcoming}' THEN 'upcoming' ELSE 'none' END";
    }

    private function tierSeveritySql(): string
    {
        [$expired, $urgent, $due, $upcoming] = $this->tierBounds();

        return "CASE WHEN expiry_date < '{$expired}' THEN 0 "
            ."WHEN expiry_date <= '{$urgent}' THEN 1 "
            ."WHEN expiry_date <= '{$due}' THEN 2 "
            ."WHEN expiry_date <= '{$upcoming}' THEN 3 ELSE 4 END";
    }
}