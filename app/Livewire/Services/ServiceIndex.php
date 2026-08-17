<?php

namespace App\Livewire\Services;

use App\Enums\ServiceStatus;
use App\Enums\ServiceType;
use App\Exports\ServicesExport;
use App\Livewire\Concerns\WithSorting;
use App\Models\Panel;
use App\Models\Service;
use App\Services\ReminderTierCalculator;
use App\Services\ServiceRenewalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class ServiceIndex extends Component
{
    use WithPagination, WithSorting;

    public string $sortBy = 'name';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'type', history: true)]
    public string $typeFilter = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = '';

    #[Url(as: 'panel', history: true)]
    public string $panelFilter = '';

    public ?Service $renewing = null;

    public bool $renewPaymentReceived = true;

    public string $renewInvoiceNumber = '';

    public string $renewNotes = '';

    public ?Service $deleting = null;

    public function mount(): void
    {
        $this->sortBy = 'expiry_date';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPanelFilter(): void
    {
        $this->resetPage();
    }

    public function confirmRenew(Service $service): void
    {
        $this->authorize('update', $service);

        $this->renewing = $service;
        $this->renewPaymentReceived = true;
        $this->renewInvoiceNumber = '';
        $this->renewNotes = '';
    }

    public function renew(): void
    {
        if (! $this->renewing) {
            return;
        }

        $this->authorize('update', $this->renewing);

        $renewal = ServiceRenewalService::renew($this->renewing, [
            'payment_received' => $this->renewPaymentReceived,
            'invoice_number' => $this->renewInvoiceNumber ?: null,
            'notes' => $this->renewNotes ?: null,
        ]);

        $this->dispatch('toast', message: "Service renewed — new expiry {$renewal->new_expiry_date?->format('M j, Y')}.", type: 'success');

        $this->renewing = null;
    }

    public function confirmDelete(Service $service): void
    {
        $this->authorize('delete', $service);

        $this->deleting = $service;
    }

    public function delete(): void
    {
        if (! $this->deleting) {
            return;
        }

        $this->authorize('delete', $this->deleting);

        $label = $this->deleting->domain_name ?: 'Service';

        $this->deleting->delete();

        $this->dispatch('toast', message: "{$label} was deleted.", type: 'success');

        $this->deleting = null;
    }

    public function exportCsv()
    {
        $services = $this->exportQuery()->get();
        $rows = $services->map(fn ($s) => [
            $s->domain_name ?: $s->hostingPlan?->name ?: 'Service #'.$s->id,
            $s->client?->name,
            $s->type->label(),
            $s->panel?->name,
            $s->hostingPlan?->name,
            $s->created_date?->toDateString(),
            $s->expiry_date?->toDateString(),
            (float) $s->company_price,
            (float) $s->client_price,
            $s->profit(),
            $s->currency,
            $s->status->label(),
        ]);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Service', 'Client', 'Type', 'Panel', 'Plan', 'Created', 'Expiry', 'Provider cost', 'Client price', 'Profit', 'Currency', 'Status']);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = 'services-'.now()->format('Y-m-d').'.csv';
        $path = tempnam(sys_get_temp_dir(), 'svc');
        file_put_contents($path, $csv);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function exportExcel()
    {
        return Excel::download(new ServicesExport($this->exportQuery()), 'services-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportPdf()
    {
        return Pdf::loadView('exports.services-pdf', [
            'services' => $this->exportQuery()->get(),
            'currency' => auth()->user()->defaultCurrency(),
        ])->download('services-'.now()->format('Y-m-d').'.pdf');
    }

    #[Computed]
    public function services()
    {
        $query = Service::query()
            ->with(['client:id,name', 'panel:id,name', 'hostingPlan:id,name,billing_cycle'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($inner) => $inner
                ->where('domain_name', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($client) => $client
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))))
            ->when($this->typeFilter !== '', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->panelFilter !== '', fn ($q) => $q->where('panel_id', (int) $this->panelFilter));

        return $this->applySorting($query, ['domain_name', 'type', 'status', 'expiry_date', 'company_price', 'client_price'])
            ->paginate(12);
    }

    private function exportQuery()
    {
        return Service::query()
            ->with(['client:id,name', 'panel:id,name', 'hostingPlan:id,name,billing_cycle'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($inner) => $inner
                ->where('domain_name', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($client) => $client
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))))
            ->when($this->typeFilter !== '', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->panelFilter !== '', fn ($q) => $q->where('panel_id', (int) $this->panelFilter))
            ->orderBy('expiry_date');
    }

    public function render()
    {
        return view('livewire.services.index', [
            'services' => $this->services,
            'types' => ServiceType::cases(),
            'statuses' => ServiceStatus::cases(),
            'panels' => Panel::query()->orderBy('name')->get(),
            'tierFor' => fn ($date) => $date ? ReminderTierCalculator::tierFor($date) : null,
        ]);
    }
}