<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceRenewal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function service(array $attributes = []): Service
    {
        $client = Client::factory()->for($this->user)->create();

        return Service::factory()->for($this->user)->for($client)->create($attributes);
    }

    private function renewal(Service $service, array $attributes = []): ServiceRenewal
    {
        return ServiceRenewal::factory()->for($service)->create(array_merge([
            'user_id' => $this->user->id,
        ], $attributes));
    }

    public function test_dashboard_counts_active_services(): void
    {
        $this->service();
        $this->service();
        $this->service(['status' => 'cancelled']);

        $stats = Livewire::test(Dashboard::class)->instance()->stats;

        $this->assertSame(2, $stats['active_services']);
    }

    public function test_dashboard_counts_total_clients(): void
    {
        Client::factory()->for($this->user)->count(3)->create();

        $stats = Livewire::test(Dashboard::class)->instance()->stats;

        $this->assertSame(3, $stats['total_clients']);
    }

    public function test_dashboard_monthly_revenue_only_counts_paid_renewals(): void
    {
        $service = $this->service();

        $this->renewal($service, [
            'payment_received' => true,
            'payment_received_date' => now()->toDateString(),
            'client_price' => 25.00,
        ]);
        $this->renewal($service, [
            'payment_received' => false,
            'payment_received_date' => null,
            'client_price' => 50.00,
        ]);
        $this->renewal($service, [
            'payment_received' => true,
            'payment_received_date' => now()->subMonthsNoOverflow(2)->toDateString(),
            'client_price' => 10.00,
        ]);

        $stats = Livewire::test(Dashboard::class)->instance()->stats;

        $this->assertSame(25.0, $stats['monthly_revenue']);
    }

    public function test_dashboard_counts_upcoming_renewals_within_thirty_days(): void
    {
        $this->service(['expiry_date' => now()->addDays(5)->toDateString()]);
        $this->service(['expiry_date' => now()->addDays(40)->toDateString()]);
        $this->service(['status' => 'cancelled', 'expiry_date' => now()->addDays(6)->toDateString()]);
        $this->service(['auto_renew_tracking' => false, 'expiry_date' => now()->addDays(7)->toDateString()]);

        $stats = Livewire::test(Dashboard::class)->instance()->stats;

        $this->assertSame(1, $stats['upcoming_renewals']);
    }

    public function test_dashboard_revenue_series_covers_last_six_months(): void
    {
        $service = $this->service();

        $this->renewal($service, [
            'payment_received' => true,
            'payment_received_date' => now()->toDateString(),
            'client_price' => 15.00,
        ]);
        $this->renewal($service, [
            'payment_received' => true,
            'payment_received_date' => now()->subMonthsNoOverflow(3)->toDateString(),
            'client_price' => 20.00,
        ]);

        $series = Livewire::test(Dashboard::class)->instance()->revenueSeries;

        $this->assertCount(6, $series['labels']);
        $this->assertCount(6, $series['values']);
        $this->assertSame(now()->format('M'), $series['labels'][5]);
        $this->assertSame(15.0, $series['values'][5]);
        $this->assertSame(20.0, $series['values'][2]);
    }

    public function test_dashboard_isolates_tenant_data(): void
    {
        $this->service(['expiry_date' => now()->addDays(3)->toDateString()]);

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $otherClient = Client::factory()->for($otherUser)->create();
        $otherService = Service::factory()->for($otherUser)->for($otherClient)->create([
            'expiry_date' => now()->addDays(2)->toDateString(),
        ]);
        ServiceRenewal::factory()->for($otherService)->create([
            'user_id' => $otherUser->id,
            'payment_received' => true,
            'payment_received_date' => now()->toDateString(),
            'client_price' => 99.00,
        ]);
        $this->actingAs($this->user);

        $stats = Livewire::test(Dashboard::class)->instance()->stats;
        $attention = Livewire::test(Dashboard::class)->instance()->attentionList;

        $this->assertSame(1, $stats['active_services']);
        $this->assertSame(1, $stats['total_clients']);
        $this->assertSame(0.0, $stats['monthly_revenue']);
        $this->assertSame(1, $stats['upcoming_renewals']);
        $this->assertCount(1, $attention);
    }

    public function test_dashboard_attention_list_sorts_urgent_services_sorted(): void
    {
        $later = $this->service(['expiry_date' => now()->addDays(9)->toDateString()]);
        $sooner = $this->service(['expiry_date' => now()->addDays(2)->toDateString()]);

        $attention = Livewire::test(Dashboard::class)->instance()->attentionList;

        $this->assertCount(2, $attention);
        $this->assertSame($sooner->id, $attention[0]->id);
        $this->assertSame($later->id, $attention[1]->id);
    }

    public function test_dashboard_renewal_snapshot_counts_tiers_within_window(): void
    {
        $this->service(['expiry_date' => now()->subDays(5)->toDateString()]);
        $this->service(['expiry_date' => now()->subDays(1)->toDateString()]);
        $this->service(['expiry_date' => now()->addDays(3)->toDateString()]);
        $this->service(['expiry_date' => now()->addDays(20)->toDateString()]);
        $this->service(['expiry_date' => now()->addDays(40)->toDateString()]);

        $component = Livewire::test(Dashboard::class)->instance();

        $this->assertSame(
            ['expired' => 2, 'urgent' => 1, 'due_soon' => 0, 'upcoming' => 1],
            $component->renewalSnapshot,
        );
        $this->assertSame(4, $component->stats['upcoming_renewals']);
    }

    public function test_dashboard_attention_list_sorts_expired_first_then_nearest(): void
    {
        $expiredOld = $this->service(['expiry_date' => now()->subDays(5)->toDateString()]);
        $expiredRecent = $this->service(['expiry_date' => now()->subDays(1)->toDateString()]);
        $urgent = $this->service(['expiry_date' => now()->addDays(3)->toDateString()]);
        $upcoming = $this->service(['expiry_date' => now()->addDays(20)->toDateString()]);

        $attention = Livewire::test(Dashboard::class)->instance()->attentionList;

        $this->assertSame(
            [$expiredRecent->id, $expiredOld->id, $urgent->id, $upcoming->id],
            $attention->pluck('id')->all(),
        );
    }

    public function test_dashboard_attention_list_filters_by_tier(): void
    {
        $this->service(['expiry_date' => now()->addDays(3)->toDateString()]);
        $this->service(['expiry_date' => now()->addDays(20)->toDateString()]);

        $component = Livewire::test(Dashboard::class);
        $component->set('attentionFilter', 'urgent');

        $this->assertCount(1, $component->instance()->attentionList);
    }
}
