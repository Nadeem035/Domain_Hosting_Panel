<?php

namespace App\Livewire\Services;

use App\Enums\ClientStatus;
use App\Enums\BillingCycle;
use App\Enums\PanelType;
use App\Enums\ServiceStatus;
use App\Enums\ServiceType;
use App\Models\Client;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class ServiceForm extends Component
{
    #[Locked]
    public ?Service $service = null;

    public string $client_id = '';

    public string $clientSearch = '';

    public bool $showClientDropdown = false;

    public string $type = 'domain';

    public string $panel_id = '';

    public string $hosting_plan_id = '';

    public string $planSearch = '';

    public bool $showPlanDropdown = false;

    public string $domain_name = '';

    public string $created_date = '';

    public string $expiry_date = '';

    public string $company_price = '';

    public string $client_price = '';

    public string $currency = '';

    public string $status = 'active';

    public bool $auto_renew_tracking = true;

    public string $notes = '';

    public bool $showClientQuickCreate = false;

    public string $quickClientName = '';

    public string $quickClientEmail = '';

    public bool $showPanelQuickCreate = false;

    public string $quickPanelName = '';

    public string $quickPanelType = 'cpanel';

    public string $quickPanelHost = '';

    public bool $showPlanQuickCreate = false;

    public string $quickPlanName = '';

    public string $quickPlanCycle = 'monthly';

    public string $quickPlanPrice = '';

    public function mount(?Service $service = null): void
    {
        if ($service?->exists) {
            $this->authorize('update', $service);

            $this->service = $service;
            $this->client_id = (string) $service->client_id;
            $this->clientSearch = $service->client?->name ?? '';
            $this->type = $service->type->value;
            $this->panel_id = $service->panel_id ? (string) $service->panel_id : '';
            $this->hosting_plan_id = $service->hosting_plan_id ? (string) $service->hosting_plan_id : '';
            $this->planSearch = $service->hostingPlan?->name ?? '';
            $this->domain_name = $service->domain_name ?? '';
            $this->created_date = $service->created_date?->toDateString() ?? '';
            $this->expiry_date = $service->expiry_date?->toDateString() ?? '';
            $this->company_price = number_format((float) $service->company_price, 2, '.', '');
            $this->client_price = number_format((float) $service->client_price, 2, '.', '');
            $this->currency = $service->currency;
            $this->status = $service->status->value;
            $this->auto_renew_tracking = $service->auto_renew_tracking;
            $this->notes = $service->notes ?? '';
        } else {
            $this->currency = auth()->user()->defaultCurrency();
        }
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')->where('user_id', auth()->id())],
            'type' => ['required', 'in:domain,hosting,both'],
            'panel_id' => [
                Rule::requiredIf(in_array($this->type, ['hosting', 'both'])),
                'nullable',
                Rule::exists('panels', 'id')->where('user_id', auth()->id()),
            ],
            'hosting_plan_id' => ['nullable', Rule::exists('hosting_plans', 'id')->where('user_id', auth()->id())],
            'domain_name' => [
                Rule::requiredIf(in_array($this->type, ['domain', 'both'])),
                'nullable',
                'string',
                'max:255',
            ],
            'created_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:created_date'],
            'company_price' => ['required', 'numeric', 'min:0'],
            'client_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['required', 'in:active,expired,cancelled,pending_renewal'],
            'auto_renew_tracking' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['client_id'] = (int) $data['client_id'];
        $data['panel_id'] = $data['panel_id'] !== '' ? (int) $data['panel_id'] : null;
        $data['hosting_plan_id'] = $data['hosting_plan_id'] !== '' ? (int) $data['hosting_plan_id'] : null;
        $data['auto_renew_tracking'] = $this->auto_renew_tracking;

        if ($this->service) {
            $this->authorize('update', $this->service);

            $this->service->update($data);

            $this->dispatch('toast', message: ($this->service->domain_name ?? 'Service').' was updated.', type: 'success');

            $this->redirect(route('services.show', $this->service), navigate: true);
        } else {
            $service = Service::create($data);

            $this->dispatch('toast', message: 'Service was created.', type: 'success');

            $this->redirect(route('services.show', $service), navigate: true);
        }
    }

    public function updatedType(): void
    {
        if (! in_array($this->type, ['hosting', 'both'])) {
            $this->hosting_plan_id = '';
            $this->planSearch = '';
        }
    }

    public function updatedPanelId(): void
    {
        $this->hosting_plan_id = '';
        $this->planSearch = '';
        $this->suggestExpiry();
    }

    public function updatedCreatedDate(): void
    {
        $this->suggestExpiry();
    }

    public function selectClient(int $id): void
    {
        $client = Client::find($id);

        if (! $client) {
            return;
        }

        $this->client_id = (string) $client->id;
        $this->clientSearch = $client->name;
        $this->showClientDropdown = false;
    }

    public function clearClient(): void
    {
        $this->client_id = '';
        $this->clientSearch = '';
    }

    public function selectPlan(int $id): void
    {
        $plan = HostingPlan::find($id);

        if (! $plan) {
            return;
        }

        $this->hosting_plan_id = (string) $plan->id;
        $this->planSearch = $plan->name;
        $this->showPlanDropdown = false;
        $this->company_price = number_format((float) $plan->price, 2, '.', '');
        $this->suggestExpiry();
    }

    public function clearPlan(): void
    {
        $this->hosting_plan_id = '';
        $this->planSearch = '';
    }

    private function suggestExpiry(): void
    {
        if (! $this->hosting_plan_id || ! $this->created_date) {
            return;
        }

        $plan = HostingPlan::find((int) $this->hosting_plan_id);

        if (! $plan || ! Carbon::createFromFormat('Y-m-d', $this->created_date)) {
            return;
        }

        $this->expiry_date = Carbon::parse($this->created_date)
            ->addMonths($plan->billing_cycle->months())
            ->toDateString();
    }

    public function openClientQuickCreate(string $name = ''): void
    {
        $this->quickClientName = $name ?: $this->clientSearch;
        $this->showClientQuickCreate = true;
    }

    public function saveQuickClient(): void
    {
        $data = $this->validate([
            'quickClientName' => ['required', 'string', 'max:255'],
            'quickClientEmail' => ['nullable', 'email', 'max:255'],
        ]);

        $client = Client::create([
            'name' => $data['quickClientName'],
            'email' => $data['quickClientEmail'] ?: null,
            'status' => ClientStatus::Active,
        ]);

        $this->client_id = (string) $client->id;
        $this->clientSearch = $client->name;
        $this->showClientQuickCreate = false;
        $this->quickClientName = '';
        $this->quickClientEmail = '';

        $this->dispatch('toast', message: "{$client->name} was added.", type: 'success');
    }

    public function openPanelQuickCreate(): void
    {
        $this->showPanelQuickCreate = true;
    }

    public function saveQuickPanel(): void
    {
        $data = $this->validate([
            'quickPanelName' => ['required', 'string', 'max:255'],
            'quickPanelType' => ['required', 'in:cpanel,whm,plesk,directadmin,other'],
            'quickPanelHost' => ['nullable', 'string', 'max:255'],
        ]);

        $panel = Panel::create([
            'name' => $data['quickPanelName'],
            'type' => $data['quickPanelType'],
            'host' => $data['quickPanelHost'] ?: null,
        ]);

        $this->panel_id = (string) $panel->id;
        $this->showPanelQuickCreate = false;
        $this->quickPanelName = '';
        $this->quickPanelHost = '';

        $this->dispatch('toast', message: "{$panel->name} was added.", type: 'success');
    }

    public function openPlanQuickCreate(): void
    {
        if (! $this->panel_id) {
            $this->dispatch('toast', message: 'Pick a panel first, then add a plan.', type: 'error');

            return;
        }

        $this->showPlanQuickCreate = true;
    }

    public function saveQuickPlan(): void
    {
        $data = $this->validate([
            'quickPlanName' => ['required', 'string', 'max:255'],
            'quickPlanCycle' => ['required', 'in:monthly,quarterly,semi_annual,annual,biennial,triennial'],
            'quickPlanPrice' => ['required', 'numeric', 'min:0'],
        ]);

        $plan = HostingPlan::create([
            'panel_id' => (int) $this->panel_id,
            'name' => $data['quickPlanName'],
            'billing_cycle' => $data['quickPlanCycle'],
            'price' => $data['quickPlanPrice'],
        ]);

        $this->hosting_plan_id = (string) $plan->id;
        $this->planSearch = $plan->name;
        $this->company_price = number_format((float) $plan->price, 2, '.', '');
        $this->showPlanQuickCreate = false;
        $this->quickPlanName = '';
        $this->quickPlanPrice = '';
        $this->suggestExpiry();

        $this->dispatch('toast', message: "{$plan->name} was added.", type: 'success');
    }

    #[Computed]
    public function clients()
    {
        return Client::query()
            ->when($this->clientSearch, fn ($q) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$this->clientSearch}%")
                ->orWhere('email', 'like', "%{$this->clientSearch}%")
                ->orWhere('company', 'like', "%{$this->clientSearch}%")))
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function plans()
    {
        return HostingPlan::query()
            ->when($this->panel_id, fn ($q) => $q->where('panel_id', (int) $this->panel_id))
            ->when($this->planSearch, fn ($q) => $q->where('name', 'like', "%{$this->planSearch}%"))
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.services.form', [
            'types' => ServiceType::cases(),
            'statuses' => ServiceStatus::cases(),
            'panels' => Panel::query()->orderBy('name')->get(),
            'cycles' => BillingCycle::cases(),
            'panelTypes' => PanelType::cases(),
        ]);
    }
}