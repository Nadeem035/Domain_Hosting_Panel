<?php

namespace App\Livewire\Billing;

use App\Models\ServiceRenewal;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InvoiceIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $currency = '';

    public int $selectedId = 0;

    public function mount(): void
    {
        $this->currency = auth()->user()?->defaultCurrency() ?: 'USD';
    }

    /**
     * Requery when filter changes.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->invoiceQuery()
            ->when($this->search !== '', fn (Builder $q) => $q->where('invoice_number', 'like', '%'.$this->search.'%'))
            ->latest('renewed_on');

        return view('livewire.billing.index', [
            'invoices' => $query->paginate(20),
            'currency' => $this->currency,
        ]);
    }

    /**
     * Tenant-scoped renewal query.
     *
     * Uses the BelongsToTenant trait on ServiceRenewal so the scope works
     * automatically for authenticated users.  Admin users see everything.
     */
    private function invoiceQuery(): Builder
    {
        $query = ServiceRenewal::query();

        if ($this->statusFilter === 'paid') {
            $query->where('payment_received', true);
        }

        if ($this->statusFilter === 'unpaid') {
            $query->where('payment_received', false);
        }

        return $query;
    }

    /**
     * Mark selected renewals as paid.
     */
    public function markPaid(): void
    {
        if ($this->selectedId <= 0) {
            $this->dispatch('toast', message: 'Please select a renewal to mark as paid.', type: 'error');

            return;
        }

        $renewal = ServiceRenewal::find($this->selectedId);

        if (! $renewal) {
            $this->dispatch('toast', message: 'Renewal not found.', type: 'error');

            return;
        }

        $renewal->update([
            'payment_received' => true,
            'payment_received_date' => now()->toDateString(),
        ]);

        $this->dispatch('toast', message: "Renewal #{$renewal->invoice_number} marked as paid.", type: 'success');

        $this->selectedId = 0;
        $this->resetPage();
    }

    public function exportCsv()
    {
        $invoices = $this->invoiceQuery()
            ->when($this->search !== '', fn (Builder $q) => $q->where('invoice_number', 'like', '%'.$this->search.'%'))
            ->get(['invoice_number', 'renewed_on', 'service_id', 'client_price', 'company_price', 'payment_received', 'payment_received_date']);

        $rows = $invoices->map(fn ($r) => [
            $r->invoice_number ?: '—',
            $r->renewed_on ?? '—',
            $r->service ? $r->service->client?->name ?? ($r->service->domain_name ?: 'Service #'.$r->service->id) : '—',
            $r->payment_received ? 'Paid' : 'Unpaid',
            number_format((float) $r->client_price, 2).' '.$this->currency,
            $r->payment_received_date ?? '—',
        ]);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Invoice #', 'Renewed', 'Service / Client', 'Status', 'Client price', 'Paid on']);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $path = tempnam(sys_get_temp_dir(), 'inv');
        file_put_contents($path, $csv);

        return response()->download($path, 'invoices-'.now()->format('Y-m-d').'.csv')->deleteFileAfterSend(true);
    }
}