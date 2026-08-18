<?php

namespace Tests\Feature;

use App\Livewire\Billing\InvoiceIndex;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceRenewal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function renewal(array $attributes = []): ServiceRenewal
    {
        $client = Client::factory()->for($this->user)->create();
        $service = Service::factory()->for($this->user)->for($client)->create();

        return ServiceRenewal::factory()->for($service)->create(array_merge([
            'user_id' => $this->user->id,
        ], $attributes));
    }

    public function test_invoices_list_only_own_renewals(): void
    {
        $this->renewal();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $otherClient = Client::factory()->for($otherUser)->create();
        $otherService = Service::factory()->for($otherUser)->for($otherClient)->create();
        ServiceRenewal::factory()->for($otherService)->create(['user_id' => $otherUser->id]);
        $this->actingAs($this->user);

        Livewire::test(InvoiceIndex::class)
            ->assertViewHas('invoices', fn ($invoices) => $invoices->total() === 1);
    }

    public function test_invoices_filter_by_paid_status(): void
    {
        $this->renewal(['invoice_number' => 'INV-PAID-1', 'payment_received' => true]);
        $this->renewal(['invoice_number' => 'INV-PAID-2', 'payment_received' => true]);
        $this->renewal(['invoice_number' => 'INV-OPEN-1', 'payment_received' => false]);

        Livewire::test(InvoiceIndex::class)
            ->set('statusFilter', 'paid')
            ->assertViewHas('invoices', fn ($invoices) => $invoices->total() === 2);

        Livewire::test(InvoiceIndex::class)
            ->set('statusFilter', 'unpaid')
            ->assertViewHas('invoices', fn ($invoices) => $invoices->total() === 1);
    }

    public function test_invoices_search_by_invoice_number(): void
    {
        $this->renewal(['invoice_number' => 'INV-12345']);
        $this->renewal(['invoice_number' => 'INV-99999']);

        Livewire::test(InvoiceIndex::class)
            ->set('search', '12345')
            ->assertViewHas('invoices', fn ($invoices) => $invoices->total() === 1
                && $invoices->first()->invoice_number === 'INV-12345');
    }

    public function test_invoices_mount_uses_user_currency(): void
    {
        $this->user->update(['notification_preferences' => ['currency' => 'EUR']]);

        Livewire::test(InvoiceIndex::class)
            ->assertSet('currency', 'EUR');
    }

    public function test_mark_paid_updates_the_renewal(): void
    {
        $renewal = $this->renewal(['invoice_number' => 'INV-OPEN-1', 'payment_received' => false]);

        Livewire::test(InvoiceIndex::class)
            ->set('selectedId', $renewal->id)
            ->call('markPaid')
            ->assertDispatched('toast')
            ->assertSet('selectedId', 0);

        $this->assertTrue($renewal->refresh()->payment_received);
        $this->assertSame(now()->toDateString(), $renewal->payment_received_date->toDateString());
    }

    public function test_mark_paid_without_selection_shows_error(): void
    {
        $renewal = $this->renewal(['payment_received' => false]);

        Livewire::test(InvoiceIndex::class)
            ->call('markPaid')
            ->assertDispatched('toast');

        $this->assertFalse($renewal->refresh()->payment_received);
    }

    public function test_mark_paid_rejects_renewal_from_another_tenant(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $otherClient = Client::factory()->for($otherUser)->create();
        $otherService = Service::factory()->for($otherUser)->for($otherClient)->create();
        $otherRenewal = ServiceRenewal::factory()->for($otherService)->create([
            'user_id' => $otherUser->id,
            'payment_received' => false,
        ]);
        $this->actingAs($this->user);

        Livewire::test(InvoiceIndex::class)
            ->set('selectedId', $otherRenewal->id)
            ->call('markPaid');

        $this->assertFalse($otherRenewal->refresh()->payment_received);
    }

    public function test_export_csv_returns_download(): void
    {
        $this->renewal(['invoice_number' => 'INV-12345']);

        Livewire::test(InvoiceIndex::class)
            ->call('exportCsv')
            ->assertFileDownloaded('invoices-'.now()->format('Y-m-d').'.csv');
    }
}