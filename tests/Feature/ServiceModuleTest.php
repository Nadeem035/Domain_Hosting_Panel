<?php

namespace Tests\Feature;

use App\Enums\BillingCycle;
use App\Enums\ServiceStatus;
use App\Livewire\Services\ServiceForm;
use App\Livewire\Services\ServiceIndex;
use App\Livewire\Services\ServiceShow;
use App\Models\Client;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function foreignService(): Service
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $client = Client::factory()->for($otherUser)->create();
        $panel = Panel::factory()->for($otherUser)->create();
        $service = Service::factory()->for($otherUser)->for($client)->for($panel)->create();
        $this->actingAs($this->user);

        return $service;
    }

    private function makePanel(): Panel
    {
        return Panel::factory()->for($this->user)->cpanel()->create(['name' => 'Shared cPanel']);
    }

    private function makePlan(Panel $panel, string $cycle = 'annual', float $price = 120): HostingPlan
    {
        return HostingPlan::factory()->for($this->user)->for($panel)->create([
            'name' => 'Business 20GB',
            'billing_cycle' => $cycle,
            'price' => $price,
        ]);
    }

    private function makeClient(): Client
    {
        return Client::factory()->for($this->user)->create(['name' => 'Acme Corp']);
    }

    public function test_service_index_lists_only_own_services(): void
    {
        $client = $this->makeClient();
        Service::factory()->for($this->user)->for($client)->count(3)->create();
        $this->foreignService();

        Livewire::test(ServiceIndex::class)
            ->assertViewHas('services', fn ($services) => $services->total() === 3);
    }

    public function test_service_index_filters(): void
    {
        $client = $this->makeClient();
        Service::factory()->for($this->user)->for($client)->create(['domain_name' => 'findme.example.com']);
        Service::factory()->for($this->user)->for($client)->hosting()->create(['expiry_date' => '2026-09-01']);
        Service::factory()->for($this->user)->for($client)->cancelled()->create();

        Livewire::test(ServiceIndex::class)
            ->set('search', 'findme')
            ->assertViewHas('services', fn ($services) => $services->total() === 1);

        Livewire::test(ServiceIndex::class)
            ->set('typeFilter', 'hosting')
            ->assertViewHas('services', fn ($services) => $services->total() === 1);

        Livewire::test(ServiceIndex::class)
            ->set('statusFilter', 'cancelled')
            ->assertViewHas('services', fn ($services) => $services->total() === 1);
    }

    public function test_service_can_be_created(): void
    {
        $client = $this->makeClient();
        $panel = $this->makePanel();
        $plan = $this->makePlan($panel);

        Livewire::test(ServiceForm::class)
            ->set('client_id', (string) $client->id)
            ->set('clientSearch', $client->name)
            ->set('type', 'hosting')
            ->set('panel_id', (string) $panel->id)
            ->set('hosting_plan_id', (string) $plan->id)
            ->set('planSearch', $plan->name)
            ->set('created_date', '2026-01-15')
            ->set('expiry_date', '2027-01-15')
            ->set('company_price', '120.00')
            ->set('client_price', '180.00')
            ->set('currency', 'USD')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('services', [
            'client_id' => $client->id,
            'panel_id' => $panel->id,
            'hosting_plan_id' => $plan->id,
            'type' => 'hosting',
            'user_id' => $this->user->id,
            'company_price' => 120.0,
            'client_price' => 180.0,
            'status' => 'active',
        ]);
    }

    public function test_service_requires_domain_for_domain_type(): void
    {
        $client = $this->makeClient();

        Livewire::test(ServiceForm::class)
            ->set('client_id', (string) $client->id)
            ->set('type', 'domain')
            ->set('domain_name', '')
            ->set('created_date', '2026-01-15')
            ->set('expiry_date', '2027-01-15')
            ->set('company_price', '10.00')
            ->set('client_price', '15.00')
            ->call('save')
            ->assertHasErrors(['domain_name' => 'required']);
    }

    public function test_service_requires_panel_for_hosting_type(): void
    {
        $client = $this->makeClient();

        Livewire::test(ServiceForm::class)
            ->set('client_id', (string) $client->id)
            ->set('type', 'hosting')
            ->set('panel_id', '')
            ->set('created_date', '2026-01-15')
            ->set('expiry_date', '2027-01-15')
            ->set('company_price', '120.00')
            ->set('client_price', '180.00')
            ->call('save')
            ->assertHasErrors(['panel_id' => 'required']);
    }

    public function test_service_can_be_updated(): void
    {
        $client = $this->makeClient();
        $service = Service::factory()->for($this->user)->for($client)->create([
            'domain_name' => 'old.example.com',
            'client_price' => 10,
        ]);

        Livewire::test(ServiceForm::class, ['service' => $service])
            ->set('domain_name', 'new.example.com')
            ->set('client_price', '15.00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('services', ['id' => $service->id, 'domain_name' => 'new.example.com', 'client_price' => 15.0]);
    }

    public function test_user_cannot_update_another_users_service(): void
    {
        $foreign = $this->foreignService();

        Livewire::test(ServiceForm::class, ['service' => $foreign])
            ->assertForbidden();

        $this->assertDatabaseHas('services', ['id' => $foreign->id, 'domain_name' => $foreign->domain_name]);
    }

    public function test_user_cannot_view_another_users_service_page(): void
    {
        $foreign = $this->foreignService();

        $this->get("/services/{$foreign->id}")->assertNotFound();
    }

    public function test_service_can_be_deleted(): void
    {
        $client = $this->makeClient();
        $service = Service::factory()->for($this->user)->for($client)->create();

        Livewire::test(ServiceIndex::class)
            ->call('confirmDelete', $service->id)
            ->call('delete')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }

    public function test_plan_selection_autofills_price_and_expiry(): void
    {
        $panel = $this->makePanel();
        $plan = $this->makePlan($panel, 'annual', 120);

        Livewire::test(ServiceForm::class)
            ->set('type', 'hosting')
            ->set('panel_id', (string) $panel->id)
            ->set('created_date', '2026-03-01')
            ->call('selectPlan', $plan->id)
            ->assertSet('hosting_plan_id', (string) $plan->id)
            ->assertSet('company_price', '120.00')
            ->assertSet('expiry_date', '2027-03-01');
    }

    public function test_quick_client_create_selects_the_client(): void
    {
        Livewire::test(ServiceForm::class)
            ->call('openClientQuickCreate', 'Brand New Co')
            ->set('quickClientName', 'Brand New Co')
            ->set('quickClientEmail', 'hello@brandnew.co')
            ->call('saveQuickClient')
            ->assertHasNoErrors()
            ->assertSet('showClientQuickCreate', false)
            ->assertSet('clientSearch', 'Brand New Co');

        $client = Client::where('name', 'Brand New Co')->first();

        $this->assertNotNull($client);
        $this->assertDatabaseHas('clients', ['name' => 'Brand New Co', 'user_id' => $this->user->id]);

        // Re-open the form and confirm selecting works against the new client.
        Livewire::test(ServiceForm::class)
            ->call('selectClient', $client->id)
            ->assertSet('client_id', (string) $client->id)
            ->assertSet('clientSearch', $client->name);
    }

    public function test_quick_panel_create_selects_the_panel(): void
    {
        Livewire::test(ServiceForm::class)
            ->set('type', 'hosting')
            ->call('openPanelQuickCreate')
            ->set('quickPanelName', 'Reseller Host #2')
            ->set('quickPanelType', 'whm')
            ->call('saveQuickPanel')
            ->assertHasNoErrors()
            ->assertSet('showPanelQuickCreate', false)
            ->assertSet('panel_id', (string) Panel::where('name', 'Reseller Host #2')->value('id'));

        $this->assertDatabaseHas('panels', ['name' => 'Reseller Host #2', 'type' => 'whm', 'user_id' => $this->user->id]);
    }

    public function test_quick_plan_create_requires_a_panel_first(): void
    {
        Livewire::test(ServiceForm::class)
            ->set('type', 'hosting')
            ->call('openPlanQuickCreate')
            ->assertSet('showPlanQuickCreate', false);

        $this->assertDatabaseCount('hosting_plans', 0);
    }

    public function test_renew_extends_expiry_and_records_renewal(): void
    {
        $client = $this->makeClient();
        $panel = $this->makePanel();
        $plan = $this->makePlan($panel, 'annual', 120);
        $service = Service::factory()->for($this->user)->for($client)->for($panel)->create([
            'hosting_plan_id' => $plan->id,
            'expiry_date' => '2026-10-01',
            'status' => ServiceStatus::PendingRenewal,
            'last_expiry_tier' => 'due_soon',
        ]);

        Livewire::test(ServiceIndex::class)
            ->call('confirmRenew', $service->id)
            ->call('renew')
            ->assertHasNoErrors();

        $renewal = \App\Models\ServiceRenewal::where('service_id', $service->id)->first();

        $this->assertNotNull($renewal);
        $this->assertSame('2026-10-01', $renewal->previous_expiry_date->toDateString());
        $this->assertSame('2027-10-01', $renewal->new_expiry_date->toDateString());
        $this->assertTrue($renewal->payment_received);
        $this->assertNull($renewal->invoice_number);

        $service->refresh();
        $this->assertSame('2027-10-01', $service->expiry_date->toDateString());
        $this->assertSame('active', $service->status->value);
        $this->assertNull($service->last_expiry_tier);
    }

    public function test_renew_can_mark_payment_as_not_received(): void
    {
        $client = $this->makeClient();
        $service = Service::factory()->for($this->user)->for($client)->create(['expiry_date' => '2026-10-01']);

        Livewire::test(ServiceIndex::class)
            ->call('confirmRenew', $service->id)
            ->set('renewPaymentReceived', false)
            ->set('renewInvoiceNumber', 'INV-999')
            ->call('renew')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('service_renewals', [
            'service_id' => $service->id,
            'payment_received' => false,
            'payment_received_date' => null,
            'invoice_number' => 'INV-999',
        ]);
    }

    public function test_renew_uses_plan_cycle_months(): void
    {
        $client = $this->makeClient();
        $panel = $this->makePanel();
        $plan = $this->makePlan($panel, 'quarterly', 30);
        $service = Service::factory()->for($this->user)->for($client)->for($panel)->create([
            'hosting_plan_id' => $plan->id,
            'expiry_date' => '2026-08-31',
        ]);

        Livewire::test(ServiceShow::class, ['service' => $service])
            ->call('renew');

        $renewal = \App\Models\ServiceRenewal::where('service_id', $service->id)->first();

        $this->assertNotNull($renewal);
        $this->assertSame('2026-08-31', $renewal->previous_expiry_date->toDateString());
        $this->assertSame('2026-11-30', $renewal->new_expiry_date->toDateString());
    }

    public function test_service_show_lists_renewal_history(): void
    {
        $client = $this->makeClient();
        $service = Service::factory()->for($this->user)->for($client)->create();
        \App\Models\ServiceRenewal::factory()->for($service)->count(3)->create();

        Livewire::test(ServiceShow::class, ['service' => $service])
            ->assertViewHas('renewals', fn ($renewals) => $renewals->total() === 3);
    }

    public function test_exports_return_downloads(): void
    {
        $client = $this->makeClient();
        Service::factory()->for($this->user)->for($client)->count(2)->create();

        $filename = 'services-'.now()->format('Y-m-d');

        Livewire::test(ServiceIndex::class)->call('exportCsv')->assertFileDownloaded($filename.'.csv');
        Livewire::test(ServiceIndex::class)->call('exportPdf')->assertFileDownloaded($filename.'.pdf');
        Livewire::test(ServiceIndex::class)->call('exportExcel')->assertFileDownloaded($filename.'.xlsx');
    }
}