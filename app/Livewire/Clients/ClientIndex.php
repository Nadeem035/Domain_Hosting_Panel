<?php

namespace App\Livewire\Clients;

use App\Enums\ClientStatus;
use App\Livewire\Concerns\WithSorting;
use App\Models\Client;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ClientIndex extends Component
{
    use WithPagination, WithSorting;

    public string $sortBy = 'name';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = '';

    public ?Client $deleting = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(Client $client): void
    {
        $this->authorize('delete', $client);

        $this->deleting = $client;
    }

    public function delete(): void
    {
        if ($this->deleting) {
            $this->authorize('delete', $this->deleting);

            $this->deleting->delete();

            $this->dispatch('toast', message: "{$this->deleting->name} was deleted.", type: 'success');

            $this->deleting = null;
        }
    }

    #[Computed]
    public function clients()
    {
        $query = Client::query()
            ->withSum(['services as active_revenue' => fn ($q) => $q->active()], 'client_price')
            ->withCount(['services as active_services_count' => fn ($q) => $q->active()])
            ->when($this->search !== '', fn ($q) => $q
                ->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('company', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter));

        return $this->applySorting($query, ['name', 'status', 'active_revenue', 'active_services_count'])
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.clients.index', [
            'clients' => $this->clients,
            'statuses' => ClientStatus::cases(),
        ]);
    }
}