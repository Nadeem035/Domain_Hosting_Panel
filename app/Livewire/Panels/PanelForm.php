<?php

namespace App\Livewire\Panels;

use App\Enums\PanelType;
use App\Models\Panel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class PanelForm extends Component
{
    #[Locked]
    public ?Panel $panel = null;

    public string $name = '';

    public string $type = 'cpanel';

    public string $host = '';

    public string $ip_address = '';

    public int $client_limit = 0;

    public string $username = '';

    public string $login_url = '';

    public bool $is_active = true;

    public string $notes = '';

    public function mount(?Panel $panel = null): void
    {
        if ($panel?->exists) {
            $this->authorize('update', $panel);

            $this->panel = $panel;
            $this->name = $panel->name;
            $this->type = $panel->type->value;
            $this->host = $panel->host ?? '';
            $this->ip_address = $panel->ip_address ?? '';
            $this->client_limit = $panel->client_limit ?? 0;
            $this->username = $panel->username ?? '';
            $this->login_url = $panel->login_url ?? '';
            $this->is_active = $panel->is_active;
            $this->notes = $panel->notes ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', array_column(PanelType::cases(), 'value'))],
            'host' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip', 'max:45'],
            'client_limit' => ['required', 'integer', 'min:0', 'max:100000'],
            'username' => ['nullable', 'string', 'max:100'],
            'login_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->panel) {
            $this->authorize('update', $this->panel);

            $this->panel->update($data);

            $this->dispatch('toast', message: "{$this->panel->name} was updated.", type: 'success');

            $this->redirect(route('panels.show', $this->panel), navigate: true);
        } else {
            $panel = Panel::create($data);

            $this->dispatch('toast', message: "{$panel->name} was created.", type: 'success');

            $this->redirect(route('panels.show', $panel), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.panels.form', [
            'types' => PanelType::cases(),
        ]);
    }
}