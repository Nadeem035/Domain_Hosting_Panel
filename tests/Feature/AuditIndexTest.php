<?php

namespace Tests\Feature;

use App\Livewire\Audit\AuditIndex;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_staff_sees_only_their_own_activity(): void
    {
        Client::factory()->for($this->user)->create();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        Client::factory()->for($otherUser)->create();
        $this->actingAs($this->user);

        Livewire::test(AuditIndex::class)
            ->assertViewHas('activities', fn ($activities) => $activities->total() === 1);
    }

    public function test_staff_sees_activity_on_their_own_records(): void
    {
        $client = Client::factory()->for($this->user)->create();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        activity()->performedOn($client)->log('edited by someone else');
        $this->actingAs($this->user);

        Livewire::test(AuditIndex::class)
            ->assertViewHas('activities', fn ($activities) => $activities->total() === 2);
    }

    public function test_admin_sees_all_activity(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin);

        Client::factory()->for($this->user)->create();
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        Client::factory()->for($otherUser)->create();
        $this->actingAs($admin);

        Livewire::test(AuditIndex::class)
            ->assertViewHas('activities', fn ($activities) => $activities->total() === 2);
    }

    public function test_search_filters_by_description(): void
    {
        $client = Client::factory()->for($this->user)->create();
        $client->update(['name' => 'Renamed Client']);

        Livewire::test(AuditIndex::class)
            ->set('search', 'updated')
            ->assertViewHas('activities', fn ($activities) => $activities->total() === 1
                && $activities->first()->event === 'updated');
    }

    public function test_subject_for_returns_label_and_route(): void
    {
        $client = Client::factory()->for($this->user)->create(['name' => 'Acme Corp']);
        $activity = Activity::where('subject_type', Client::class)->where('subject_id', $client->id)->first();

        $component = Livewire::test(AuditIndex::class)->instance();
        $result = $component->subjectFor($activity);

        $this->assertSame('Acme Corp', $result['label']);
        $this->assertSame(route('clients.show', $client), $result['url']);
    }

    public function test_changed_fields_returns_updated_attributes(): void
    {
        $client = Client::factory()->for($this->user)->create();
        $client->update(['name' => 'New Name']);

        $activity = Activity::where('subject_type', Client::class)->where('event', 'updated')->first();
        $component = Livewire::test(AuditIndex::class)->instance();

        $this->assertContains('name', $component->changedFields($activity));
        $this->assertSame([], $component->changedFields(Activity::where('event', 'created')->first()));
    }
}