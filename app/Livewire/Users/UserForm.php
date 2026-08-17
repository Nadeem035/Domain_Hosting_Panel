<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserForm extends Component
{
    #[Locked]
    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $company_name = '';

    public string $timezone = 'UTC';

    public string $theme_preference = 'system';

    public string $role = 'reseller';

    public function mount(?User $user = null): void
    {
        $this->authorize('manage-users');

        if ($user?->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->company_name = $user->company_name ?? '';
            $this->timezone = $user->timezone ?? 'UTC';
            $this->theme_preference = $user->theme_preference ?? 'system';
            $this->role = $user->roles->first()?->name ?? 'reseller';
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email' . ($this->user ? ',' . $this->user->id : '')],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64'],
            'theme_preference' => ['required', 'in:light,dark,system'],
            'role' => ['required', 'in:admin,staff,reseller'],
        ];
    }

    public function save(): void
    {
        $this->authorize('manage-users');

        if ($this->user && $this->user->id === auth()->id() && $this->role !== 'admin') {
            $this->addError('role', 'You cannot remove your own admin role.');

            return;
        }

        $rules = $this->rules();

        if ($this->user && $this->password === '') {
            unset($rules['password']);
        }

        $data = $this->validate($rules);

        $role = Role::findOrCreate($this->role, 'web');

        if ($this->user) {
            unset($data['password']);

            $this->user->update($data);

            $this->user->syncRoles([$role]);

            $this->dispatch('toast', message: "{$this->user->name} was updated.", type: 'success');
        } else {
            $data['password'] = $this->password;

            $user = User::create($data);

            $user->forceFill(['email_verified_at' => now()])->save();

            $user->syncRoles([$role]);

            $this->dispatch('toast', message: "{$user->name} was created.", type: 'success');
        }

        $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.users.form', [
            'roles' => Role::orderBy('name')->get(),
            'timezones' => $this->timezones(),
        ]);
    }

    private function timezones(): array
    {
        return ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Singapore', 'Asia/Tokyo', 'Australia/Sydney'];
    }
}