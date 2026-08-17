<?php

namespace App\Livewire\Panels;

use App\Enums\PanelType;
use App\Models\Panel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PanelIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'type', history: true)]
    public string $typeFilter = '';

    public ?Panel $deleting = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(Panel $panel): void
    {
        $this->authorize('delete', $panel);

        $this->deleting = $panel;
    }

    public function delete(): void
    {
        if (! $this->deleting) {
            return;
        }

        $this->authorize('delete', $this->deleting);

        $name = $this->deleting->name;

        $this->deleting->delete();

        $this->dispatch('toast', message: "{$name} was deleted.", type: 'success');

        $this->deleting = null;
    }

    #[Computed]
    public function panels()
    {
        return Panel::query()
            ->withCount(['hostingPlans', 'services' => fn ($q) => $q->where('status', 'active')])
            ->when($this->search, fn ($q) => $q
                ->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('host', 'like', "%{$this->search}%")
                    ->orWhere('ip_address', 'like', "%{$this->search}%")))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderBy('name')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.panels.index', [
            'panels' => $this->panels,
            'types' => PanelType::cases(),
        ]);
    }
}