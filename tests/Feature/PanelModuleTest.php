<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\PanelType;
use App\Livewire\Panels\PanelForm;
use App\Livewire\Panels\PanelIndex;
use App\Livewire\Panels\PanelShow;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function foreignPanel(): Panel
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $panel = Panel::factory()->for($otherUser)->create();
        $this->actingAs($this->user);

        return $panel;
    }

    public function test_panel_index_lists_only_own_panels(): void
    {
        Panel::factory()->for($this->user)->count(3)->create();
        $this->foreignPanel();

        Livewire::test(PanelIndex::class)
            ->assertViewHas('panels', fn ($panels) => $panels->total() === 3);
    }

    public function test_panel_index_filters_by_search_and_type(): void
    {
        Panel::factory()->for($this->user)->cpanel()->create(['name' => 'Shared cPanel', 'host' => 'server1.example.com']);
        Panel::factory()->for($this->user)->create(['name' => 'Plesk VPS', 'type' => PanelType::Plesk]);
        Panel::factory()->for($this->user)->other()->create(['name' => 'Registrar API']);

        Livewire::test(PanelIndex::class)
            ->set('search', 'server1')
            ->assertViewHas('panels', fn ($panels) => $panels->total() === 1 && $panels->first()->name === 'Shared cPanel');

        Livewire::test(PanelIndex::class)
            ->set('typeFilter', 'plesk')
            ->assertViewHas('panels', fn ($panels) => $panels->total() === 1 && $panels->first()->name === 'Plesk VPS');
    }

    public function test_panel_can_be_created(): void
    {
        Livewire::test(PanelForm::class)
            ->set('name', 'Shared cPanel #1')
            ->set('type', 'cpanel')
            ->set('host', 'server1.example.com')
            ->set('ip_address', '203.0.113.10')
            ->set('username', 'root')
            ->set('client_limit', 100)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('panels', [
            'name' => 'Shared cPanel #1',
            'host' => 'server1.example.com',
            'ip_address' => '203.0.113.10',
            'username' => 'root',
            'client_limit' => 100,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_panel_requires_name(): void
    {
        Livewire::test(PanelForm::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_panel_rejects_invalid_ip(): void
    {
        Livewire::test(PanelForm::class)
            ->set('name', 'Bad IP Panel')
            ->set('ip_address', 'not-an-ip')
            ->call('save')
            ->assertHasErrors(['ip_address' => 'ip']);
    }

    public function test_panel_can_be_updated(): void
    {
        $panel = Panel::factory()->for($this->user)->create(['name' => 'Old Panel']);

        Livewire::test(PanelForm::class, ['panel' => $panel])
            ->set('name', 'Renamed Panel')
            ->set('is_active', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('panels', ['id' => $panel->id, 'name' => 'Renamed Panel', 'is_active' => false]);
    }

    public function test_user_cannot_update_another_users_panel(): void
    {
        $foreign = $this->foreignPanel();

        Livewire::test(PanelForm::class, ['panel' => $foreign])
            ->assertForbidden();

        $this->assertDatabaseHas('panels', ['id' => $foreign->id, 'name' => $foreign->name]);
    }

    public function test_user_cannot_view_another_users_panel_page(): void
    {
        $foreign = $this->foreignPanel();

        $this->get("/panels/{$foreign->id}")->assertNotFound();
    }

    public function test_panel_can_be_deleted(): void
    {
        $panel = Panel::factory()->for($this->user)->create();

        Livewire::test(PanelIndex::class)
            ->call('confirmDelete', $panel->id)
            ->call('delete')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('panels', ['id' => $panel->id]);
    }

    public function test_panel_show_lists_plans_and_stats(): void
    {
        $panel = Panel::factory()->for($this->user)->create();
        HostingPlan::factory()->for($this->user)->for($panel)->count(2)->create([
            'billing_cycle' => BillingCycle::Annual,
            'price' => 120,
        ]);
        HostingPlan::factory()->for($this->user)->for($panel)->inactive()->create();

        Livewire::test(PanelShow::class, ['panel' => $panel])
            ->assertViewHas('plans', fn ($plans) => $plans->count() === 3)
            ->assertViewHas('stats', fn ($stats) => $stats['plan_count'] === 3 && $stats['active_plan_count'] === 2);
    }

    public function test_plan_can_be_added_to_panel(): void
    {
        $panel = Panel::factory()->for($this->user)->create();

        Livewire::test(PanelShow::class, ['panel' => $panel])
            ->call('startAddPlan')
            ->set('planName', 'Business 20GB')
            ->set('planCycle', 'annual')
            ->set('planPrice', '120.00')
            ->set('planDiskSpace', '20 GB')
            ->set('planBandwidth', '100 GB')
            ->set('planFeaturesText', 'SSL certificate, Daily backups')
            ->call('savePlan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('hosting_plans', [
            'panel_id' => $panel->id,
            'name' => 'Business 20GB',
            'billing_cycle' => 'annual',
            'price' => 120.0,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_plan_requires_name_and_price(): void
    {
        $panel = Panel::factory()->for($this->user)->create();

        Livewire::test(PanelShow::class, ['panel' => $panel])
            ->call('startAddPlan')
            ->set('planName', '')
            ->set('planPrice', '')
            ->call('savePlan')
            ->assertHasErrors(['planName' => 'required', 'planPrice' => 'required']);

        $this->assertDatabaseCount('hosting_plans', 0);
    }

    public function test_plan_can_be_updated(): void
    {
        $panel = Panel::factory()->for($this->user)->create();
        $plan = HostingPlan::factory()->for($this->user)->for($panel)->create(['name' => 'Starter', 'price' => 5]);

        Livewire::test(PanelShow::class, ['panel' => $panel])
            ->call('startEditPlan', $plan->id)
            ->set('planName', 'Starter Plus')
            ->set('planPrice', '7.50')
            ->call('savePlan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('hosting_plans', ['id' => $plan->id, 'name' => 'Starter Plus', 'price' => 7.5]);
    }

    public function test_plan_can_be_deleted(): void
    {
        $panel = Panel::factory()->for($this->user)->create();
        $plan = HostingPlan::factory()->for($this->user)->for($panel)->create();

        Livewire::test(PanelShow::class, ['panel' => $panel])
            ->call('confirmDeletePlan', $plan->id)
            ->call('deletePlan')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('hosting_plans', ['id' => $plan->id]);
    }

    public function test_plan_with_services_cannot_be_deleted(): void
    {
        $panel = Panel::factory()->for($this->user)->create();
        $plan = HostingPlan::factory()->for($this->user)->for($panel)->create();
        Service::factory()->for($this->user)->for($panel)->create(['hosting_plan_id' => $plan->id]);

        Livewire::test(PanelShow::class, ['panel' => $panel])
            ->call('confirmDeletePlan', $plan->id)
            ->call('deletePlan');

        $this->assertDatabaseHas('hosting_plans', ['id' => $plan->id]);
    }

    public function test_deleting_panel_keeps_plans_but_unlinks_them(): void
    {
        $panel = Panel::factory()->for($this->user)->create();
        $plan = HostingPlan::factory()->for($this->user)->for($panel)->create();

        $panel->delete();

        $this->assertDatabaseMissing('panels', ['id' => $panel->id]);
        $this->assertDatabaseHas('hosting_plans', ['id' => $plan->id, 'panel_id' => null]);
    }
}