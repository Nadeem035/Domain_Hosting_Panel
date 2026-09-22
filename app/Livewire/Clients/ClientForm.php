<?php

namespace App\Livewire\Clients;

use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class ClientForm extends Component
{
    protected $messages = [
        'email.unique' => 'A client with this email already exists. Pick them from the list instead of adding a duplicate.',
    ];
    #[Locked]
    public ?Client $client = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company = '';

    public string $address = '';

    public string $notes = '';

    public string $status = 'active';

    public function mount(?Client $client = null): void
    {
        if ($client?->exists) {
            $this->authorize('update', $client);

            $this->client = $client;
            $this->name = $client->name;
            $this->email = $client->email ?? '';
            $this->phone = $client->phone ?? '';
            $this->company = $client->company ?? '';
            $this->address = $client->address ?? '';
            $this->notes = $client->notes ?? '';
            $this->status = $client->status->value;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clients', 'email')->where(fn ($query) => Client::tenantScopeQuery($query))->ignore($this->client?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('toast', message: $e->validator->errors()->first(), type: 'error');

            throw $e;
        }

        if ($this->client) {
            $this->authorize('update', $this->client);

            $this->client->update($data);

            $this->dispatch('toast', message: "{$this->client->name} was updated.", type: 'success');

            $this->redirect(route('clients.show', $this->client), navigate: true);
        } else {
            $client = Client::create($data);

            $this->dispatch('toast', message: "{$client->name} was created.", type: 'success');

            $this->redirect(route('clients.show', $client), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.clients.form', [
            'statuses' => ClientStatus::cases(),
        ]);
    }
}