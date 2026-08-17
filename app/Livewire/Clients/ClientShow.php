<?php

namespace App\Livewire\Clients;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Services\ReminderTierCalculator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app')]
class ClientShow extends Component
{
    #[Locked]
    public Client $client;

    public ?Client $deleting = null;

    public function mount(Client $client): void
    {
        if (! $client->exists) {
            abort(404);
        }

        $this->authorize('view', $client);

        $this->client = $client;
    }

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->client);

        $this->deleting = $this->client;
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->client);

        $name = $this->client->name;

        $this->client->delete();

        $this->dispatch('toast', message: "{$name} was deleted.", type: 'success');

        $this->redirect(route('clients.index'), navigate: true);
    }

    #[Computed]
    public function services(): Collection
    {
        return $this->client->services()
            ->with(['panel:id,name', 'hostingPlan:id,name'])
            ->orderByDesc('expiry_date')
            ->get()
            ->map(function ($service) {
                $tier = ReminderTierCalculator::tierFor($service->expiry_date);

                return [
                    'id' => $service->id,
                    'domain_name' => $service->domain_name,
                    'type' => $service->type,
                    'panel_name' => $service->panel?->name,
                    'plan_name' => $service->hostingPlan?->name,
                    'expiry_date' => $service->expiry_date,
                    'days_left' => ReminderTierCalculator::daysLeft($service->expiry_date),
                    'tier' => $tier,
                    'client_price' => $service->client_price,
                    'currency' => $service->currency,
                    'status' => $service->status,
                    'auto_renew_tracking' => $service->auto_renew_tracking,
                ];
            });
    }

    #[Computed]
    public function stats(): array
    {
        $services = $this->client->services()->get();
        $active = $services->where('status', 'active');

        return [
            'active_count' => $active->count(),
            'total_count' => $services->count(),
            'monthly_revenue' => (float) $active->sum('client_price'),
            'next_expiry' => $active->sortBy('expiry_date')->first()?->expiry_date,
        ];
    }

    #[Computed]
    public function recentActivity(): Collection
    {
        return Activity::query()
            ->where('causer_id', auth()->id())
            ->where(function ($q) {
                $q->where(fn ($inner) => $inner
                    ->where('subject_type', Client::class)
                    ->where('subject_id', $this->client->id))
                    ->orWhere(fn ($inner) => $inner
                        ->where('subject_type', \App\Models\Service::class)
                        ->whereIn('subject_id', $this->client->services()->pluck('id')));
            })
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.clients.show', [
            'services' => $this->services,
            'stats' => $this->stats,
            'recentActivity' => $this->recentActivity,
        ]);
    }
}