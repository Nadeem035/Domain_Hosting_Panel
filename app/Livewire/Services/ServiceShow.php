<?php

namespace App\Livewire\Services;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Services\ReminderTierCalculator;
use App\Services\ServiceRenewalService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class ServiceShow extends Component
{
    #[Locked]
    public Service $service;

    public bool $renewPaymentReceived = true;

    public string $renewInvoiceNumber = '';

    public string $renewNotes = '';

    public bool $confirmingDelete = false;

    public function mount(Service $service): void
    {
        abort_unless($service->exists, 404);

        $this->authorize('view', $service);

        $this->service = $service->load(['client', 'panel', 'hostingPlan']);
    }

    public function renew(): void
    {
        $this->authorize('update', $this->service);

        $renewal = ServiceRenewalService::renew($this->service, [
            'payment_received' => $this->renewPaymentReceived,
            'invoice_number' => $this->renewInvoiceNumber ?: null,
            'notes' => $this->renewNotes ?: null,
        ]);

        $this->dispatch('toast', message: "Service renewed — new expiry {$renewal->new_expiry_date?->format('M j, Y')}.", type: 'success');

        $this->renewPaymentReceived = true;
        $this->renewInvoiceNumber = '';
        $this->renewNotes = '';
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->service);

        $label = $this->service->domain_name ?: 'Service';

        $this->service->delete();

        $this->dispatch('toast', message: "{$label} was deleted.", type: 'success');

        $this->redirect(route('services.index'), navigate: true);
    }

    #[Computed]
    public function renewals()
    {
        return $this->service->renewals()->paginate(10);
    }

    #[Computed]
    public function tier()
    {
        return $this->service->expiry_date ? ReminderTierCalculator::tierFor($this->service->expiry_date) : null;
    }

    #[Computed]
    public function daysLeft()
    {
        return $this->service->expiry_date ? ReminderTierCalculator::daysLeft($this->service->expiry_date) : null;
    }

    public function render()
    {
        return view('livewire.services.show', [
            'renewals' => $this->renewals,
            'tier' => $this->tier,
            'daysLeft' => $this->daysLeft,
        ]);
    }
}