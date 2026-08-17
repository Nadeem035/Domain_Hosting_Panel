<?php

namespace App\Livewire\Panels;

use App\Enums\BillingCycle;
use App\Models\HostingPlan;
use App\Models\Panel;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class PanelShow extends Component
{
    #[Locked]
    public Panel $panel;

    public ?HostingPlan $deletingPlan = null;

    public bool $showPlanForm = false;

    public ?int $editingPlanId = null;

    public string $planName = '';

    public string $planCycle = 'monthly';

    public string $planPrice = '';

    public string $planDiskSpace = '';

    public string $planBandwidth = '';

    public string $planFeaturesText = '';

    public string $planDescription = '';

    public bool $planIsActive = true;

    public string $planNotes = '';

    public function mount(Panel $panel): void
    {
        if (! $panel->exists) {
            abort(404);
        }

        $this->authorize('view', $panel);

        $this->panel = $panel;
    }

    public function startAddPlan(): void
    {
        $this->authorize('create', HostingPlan::class);

        $this->resetPlanForm();

        $this->showPlanForm = true;
        $this->editingPlanId = null;
    }

    public function startEditPlan(HostingPlan $plan): void
    {
        $this->authorize('update', $plan);

        $this->planName = $plan->name;
        $this->planCycle = $plan->billing_cycle->value;
        $this->planPrice = number_format($plan->price, 2, '.', '');
        $this->planDiskSpace = $plan->disk_space ?? '';
        $this->planBandwidth = $plan->bandwidth ?? '';
        $this->planFeaturesText = implode(', ', $plan->features ?? []);
        $this->planDescription = $plan->description ?? '';
        $this->planIsActive = $plan->is_active;
        $this->planNotes = $plan->notes ?? '';

        $this->editingPlanId = $plan->id;
        $this->showPlanForm = true;
    }

    public function cancelPlanForm(): void
    {
        $this->showPlanForm = false;
        $this->editingPlanId = null;
        $this->resetPlanForm();
    }

    public function savePlan(): void
    {
        $data = $this->validate([
            'planName' => ['required', 'string', 'max:255'],
            'planCycle' => ['required', 'in:'.implode(',', array_column(BillingCycle::cases(), 'value'))],
            'planPrice' => ['required', 'numeric', 'min:0', 'max:999999'],
            'planDiskSpace' => ['nullable', 'string', 'max:50'],
            'planBandwidth' => ['nullable', 'string', 'max:50'],
            'planFeaturesText' => ['nullable', 'string', 'max:1000'],
            'planDescription' => ['nullable', 'string', 'max:255'],
            'planIsActive' => ['boolean'],
            'planNotes' => ['nullable', 'string'],
        ]);

        $attributes = [
            'name' => $data['planName'],
            'billing_cycle' => $data['planCycle'],
            'price' => $data['planPrice'],
            'disk_space' => $data['planDiskSpace'] ?: null,
            'bandwidth' => $data['planBandwidth'] ?: null,
            'features' => $this->splitFeatures($data['planFeaturesText']),
            'description' => $data['planDescription'] ?: null,
            'is_active' => $data['planIsActive'],
            'notes' => $data['planNotes'] ?: null,
        ];

        if ($this->editingPlanId) {
            $plan = $this->panel->hostingPlans()->findOrFail($this->editingPlanId);

            $this->authorize('update', $plan);

            $plan->update($attributes);

            $this->dispatch('toast', message: "{$plan->name} was updated.", type: 'success');
        } else {
            $plan = $this->panel->hostingPlans()->create($attributes);

            $this->dispatch('toast', message: "{$plan->name} was added.", type: 'success');
        }

        $this->cancelPlanForm();
    }

    public function confirmDeletePlan(HostingPlan $plan): void
    {
        $this->authorize('delete', $plan);

        $this->deletingPlan = $plan;
    }

    public function deletePlan(): void
    {
        if (! $this->deletingPlan) {
            return;
        }

        $this->authorize('delete', $this->deletingPlan);

        if ($this->deletingPlan->services()->exists()) {
            $this->dispatch('toast', message: 'This plan is attached to services and cannot be deleted.', type: 'error');

            $this->deletingPlan = null;

            return;
        }

        $name = $this->deletingPlan->name;

        $this->deletingPlan->delete();

        $this->dispatch('toast', message: "{$name} was deleted.", type: 'success');

        $this->deletingPlan = null;
    }

    #[Computed]
    public function plans(): Collection
    {
        return $this->panel->hostingPlans()
            ->withCount('services')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $plans = $this->panel->hostingPlans()->get();

        return [
            'plan_count' => $plans->count(),
            'active_plan_count' => $plans->where('is_active', true)->count(),
            'service_count' => $this->panel->services()->where('status', 'active')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.panels.show', [
            'plans' => $this->plans,
            'stats' => $this->stats,
            'cycles' => BillingCycle::cases(),
        ]);
    }

    private function splitFeatures(string $text): array
    {
        return collect(explode(',', $text))
            ->map(fn ($f) => trim($f))
            ->filter()
            ->values()
            ->all();
    }

    private function resetPlanForm(): void
    {
        $this->planName = '';
        $this->planCycle = 'monthly';
        $this->planPrice = '';
        $this->planDiskSpace = '';
        $this->planBandwidth = '';
        $this->planFeaturesText = '';
        $this->planDescription = '';
        $this->planIsActive = true;
        $this->planNotes = '';
    }
}