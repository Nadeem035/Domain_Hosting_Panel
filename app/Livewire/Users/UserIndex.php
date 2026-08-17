<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\WithSorting;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination, WithSorting;

    public string $sortBy = 'name';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'role', history: true)]
    public string $roleFilter = '';

    public ?User $deleting = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(User $user): void
    {
        $this->authorize('manage-users');

        if ($user->id === auth()->id()) {
            $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');

            return;
        }

        $this->deleting = $user;
    }

    public function delete(): void
    {
        $this->authorize('manage-users');

        if (! $this->deleting) {
            return;
        }

        if ($this->deleting->id === auth()->id()) {
            $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');

            return;
        }

        if ($this->deleting->hasRole('admin')
            && ! User::role('admin')->where('id', '!=', $this->deleting->id)->exists()) {
            $this->dispatch('toast', message: 'Cannot delete the last admin.', type: 'error');

            return;
        }

        $name = $this->deleting->name;

        $this->deleting->delete();

        $this->dispatch('toast', message: "{$name} was deleted.", type: 'success');

        $this->deleting = null;
    }

    #[Computed]
    public function users()
    {
        $query = User::query()
            ->with('roles')
            ->withCount(['clients as clients_count'])
            ->when($this->search !== '', fn ($q) => $q
                ->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('company_name', 'like', "%{$this->search}%")))
            ->when($this->roleFilter !== '', fn ($q) => $q->role($this->roleFilter));

        return $this->applySorting($query, ['name', 'email', 'company_name', 'timezone', 'clients_count', 'created_at'])
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => $this->users,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}