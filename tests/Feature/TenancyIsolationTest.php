<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Panel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
    }

    public function test_global_scope_filters_records_to_owning_user(): void
    {
        $clientA = Client::factory()->for($this->userA)->create();
        Client::factory()->for($this->userB)->create();

        $this->actingAs($this->userA);

        $this->assertSame(1, Client::count());
        $this->assertTrue(Client::find($clientA->id)->is($clientA));

        $this->actingAs($this->userB);

        $this->assertSame(1, Client::count());
        $this->assertNull(Client::find($clientA->id));
    }

    public function test_global_scope_applies_to_related_models(): void
    {
        $panelA = Panel::factory()->for($this->userA)->create();
        Panel::factory()->for($this->userB)->create();
        Service::factory()->for($this->userA)->for($panelA)->for(Client::factory()->for($this->userA))->create();

        $this->actingAs($this->userA);

        $this->assertSame(1, Panel::count());
        $this->assertSame(1, Service::count());
    }

    public function test_route_binding_cannot_resolve_another_users_model(): void
    {
        $clientA = Client::factory()->for($this->userA)->create();

        $this->actingAs($this->userB)
            ->get("/clients/{$clientA->id}")
            ->assertNotFound();
    }

    public function test_policy_denies_access_to_another_users_model(): void
    {
        $clientA = Client::factory()->for($this->userA)->create();
        $serviceA = Service::factory()->for($this->userA)->for($clientA)->for(Panel::factory()->for($this->userA))->create();

        $this->assertFalse($this->userB->can('view', $clientA));
        $this->assertFalse($this->userB->can('update', $clientA));
        $this->assertFalse($this->userB->can('delete', $clientA));
        $this->assertFalse($this->userB->can('view', $serviceA));
        $this->assertFalse($this->userB->can('update', $serviceA));

        $this->assertTrue($this->userA->can('view', $clientA));
        $this->assertTrue($this->userA->can('update', $serviceA));
    }

    public function test_creating_tenant_model_auto_sets_user_id(): void
    {
        $this->actingAs($this->userA);

        $client = Client::create(['name' => 'Acme Corp']);

        $this->assertSame($this->userA->id, $client->user_id);
    }

    public function test_created_model_cannot_impersonate_another_user(): void
    {
        $this->actingAs($this->userA);

        $client = Client::create(['name' => 'Hacker', 'user_id' => $this->userB->id]);

        $this->assertSame($this->userA->id, $client->user_id);
    }
}