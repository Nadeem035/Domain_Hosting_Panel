<?php

namespace Tests\Feature;

use App\Livewire\Reports\ReportIndex;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function trackedService(array $attributes = []): Service
    {
        $client = Client::factory()->for($this->user)->create();

        return Service::factory()->for($this->user)->for($client)->create($attributes);
    }

    public function test_report_lists_only_own_tracked_services(): void
    {
        $this->trackedService(['expiry_date' => now()->addDays(3)->toDateString()]);
        $this->trackedService(['expiry_date' => now()->addDays(40)->toDateString()]);

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        Service::factory()->for($otherUser)->create(['expiry_date' => now()->addDays(2)->toDateString()]);
        $this->actingAs($this->user);

        Livewire::test(ReportIndex::class)
            ->assertViewHas('services', fn ($services) => $services->total() === 2);
    }

    public function test_report_excludes_cancelled_and_untracked_services(): void
    {
        $this->trackedService(['expiry_date' => now()->addDays(3)->toDateString()]);
        $this->trackedService(['status' => 'cancelled', 'expiry_date' => now()->addDays(4)->toDateString()]);
        $this->trackedService(['auto_renew_tracking' => false, 'expiry_date' => now()->addDays(5)->toDateString()]);

        Livewire::test(ReportIndex::class)
            ->assertViewHas('services', fn ($services) => $services->total() === 1);
    }

    public function test_report_counts_tiers(): void
    {
        $this->trackedService(['expiry_date' => now()->subDays(5)->toDateString()]);
        $this->trackedService(['expiry_date' => now()->addDays(2)->toDateString()]);
        $this->trackedService(['expiry_date' => now()->addDays(10)->toDateString()]);
        $this->trackedService(['expiry_date' => now()->addDays(20)->toDateString()]);
        $this->trackedService(['expiry_date' => now()->addDays(60)->toDateString()]);

        Livewire::test(ReportIndex::class)
            ->assertViewHas('tierCounts', fn ($counts) => $counts === [
                'expired' => 1,
                'urgent' => 1,
                'due_soon' => 1,
                'upcoming' => 1,
                'none' => 1,
            ]);
    }

    public function test_report_filters_by_tier(): void
    {
        $this->trackedService(['expiry_date' => now()->addDays(3)->toDateString()]);
        $this->trackedService(['expiry_date' => now()->addDays(20)->toDateString()]);

        Livewire::test(ReportIndex::class)
            ->set('tierFilter', 'urgent')
            ->assertViewHas('services', fn ($services) => $services->total() === 1
                && $services->first()->expiry_date->toDateString() === now()->addDays(3)->toDateString());
    }

    public function test_report_sorts_by_tier_severity(): void
    {
        $this->trackedService(['domain_name' => 'expired.example.com', 'expiry_date' => now()->subDays(2)->toDateString()]);
        $this->trackedService(['domain_name' => 'upcoming.example.com', 'expiry_date' => now()->addDays(20)->toDateString()]);
        $this->trackedService(['domain_name' => 'urgent.example.com', 'expiry_date' => now()->addDays(3)->toDateString()]);

        Livewire::test(ReportIndex::class)
            ->call('sortBy', 'tier')
            ->assertViewHas('services', fn ($services) => $services->first()->domain_name === 'expired.example.com');
    }
}