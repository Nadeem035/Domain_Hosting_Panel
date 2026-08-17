<?php

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_client_index_lists_only_own_clients(): void
    {
        Client::factory()->for($this->user)->count(3)->create();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        Client::factory()->for($otherUser)->count(2)->create();
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Clients\ClientIndex::class)
            ->assertViewHas('clients', fn ($clients) => $clients->total() === 3);
    }

    public function test_client_index_filters_by_search_and_status(): void
    {
        Client::factory()->for($this->user)->create(['name' => 'Acme Corp', 'status' => ClientStatus::Active]);
        Client::factory()->for($this->user)->create(['name' => 'Globex', 'status' => ClientStatus::Inactive]);

        Livewire::test(\App\Livewire\Clients\ClientIndex::class)
            ->set('search', 'acme')
            ->assertViewHas('clients', fn ($clients) => $clients->total() === 1 && $clients->first()->name === 'Acme Corp');

        Livewire::test(\App\Livewire\Clients\ClientIndex::class)
            ->set('statusFilter', 'inactive')
            ->assertViewHas('clients', fn ($clients) => $clients->total() === 1 && $clients->first()->name === 'Globex');
    }

    public function test_client_can_be_created(): void
    {
        Livewire::test(\App\Livewire\Clients\ClientForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('company', 'Acme Ltd')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name' => 'Jane Doe',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_client_requires_name(): void
    {
        Livewire::test(\App\Livewire\Clients\ClientForm::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_client_can_be_updated(): void
    {
        $client = Client::factory()->for($this->user)->create(['name' => 'Old Name']);

        Livewire::test(\App\Livewire\Clients\ClientForm::class, ['client' => $client])
            ->set('name', 'New Name')
            ->set('status', 'inactive')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'New Name', 'status' => 'inactive']);
    }

    public function test_client_soft_deleted_from_index(): void
    {
        $client = Client::factory()->for($this->user)->create();

        Livewire::test(\App\Livewire\Clients\ClientIndex::class)
            ->call('confirmDelete', $client->id)
            ->call('delete')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_user_cannot_update_another_users_client(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $other = Client::factory()->for($otherUser)->create();
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Clients\ClientForm::class, ['client' => $other])
            ->assertForbidden();

        $this->assertDatabaseHas('clients', ['id' => $other->id, 'name' => $other->name]);
    }

    public function test_user_cannot_view_another_users_client_page(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $other = Client::factory()->for($otherUser)->create();
        $this->actingAs($this->user);

        $this->get("/clients/{$other->id}")->assertNotFound();
    }

    public function test_client_show_shows_services_and_revenue(): void
    {
        $client = Client::factory()->for($this->user)->create();
        Service::factory()->for($this->user)->for($client)->create([
            'client_price' => 120,
            'expiry_date' => now()->addDays(20),
        ]);
        Service::factory()->for($this->user)->for($client)->create([
            'client_price' => 50,
            'expiry_date' => now()->addDays(40),
        ]);
        Service::factory()->for($this->user)->for($client)->cancelled()->create(['client_price' => 99]);

        Livewire::test(\App\Livewire\Clients\ClientShow::class, ['client' => $client])
            ->assertViewHas('services', fn ($services) => $services->count() === 3)
            ->assertViewHas('stats', fn ($stats) => $stats['active_count'] === 2 && $stats['monthly_revenue'] === 170.0);
    }
}