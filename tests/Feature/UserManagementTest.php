<?php

namespace Tests\Feature;

use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserIndex;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        return $admin;
    }

    public function test_admin_bypasses_tenant_scope_and_sees_everyones_data(): void
    {
        $admin = $this->admin();
        $reseller = User::factory()->create();
        $resellerClient = Client::factory()->for($reseller)->create();
        Client::factory()->for($admin)->create();

        $this->actingAs($admin);

        $this->assertSame(2, Client::count());
        $this->assertTrue(Client::find($resellerClient->id)->is($resellerClient));
    }

    public function test_tenant_isolation_still_applies_to_non_admins(): void
    {
        $admin = $this->admin();
        $reseller = User::factory()->create();
        Client::factory()->for($admin)->count(2)->create();
        Client::factory()->for($reseller)->count(3)->create();

        $this->actingAs($reseller);

        $this->assertSame(3, Client::count());
    }

    public function test_users_page_requires_admin(): void
    {
        $reseller = User::factory()->create();

        $this->actingAs($reseller)->get('/users')->assertForbidden();

        $this->actingAs($this->admin())->get('/users')->assertOk();
    }

    public function test_admin_index_lists_all_users(): void
    {
        $this->actingAs($this->admin());
        User::factory()->count(3)->create();

        Livewire::test(UserIndex::class)
            ->assertViewHas('users', fn ($users) => $users->total() === 4);
    }

    public function test_admin_can_create_user_with_role_and_verified_email(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(UserForm::class)
            ->set('name', 'Jane Staff')
            ->set('email', 'jane@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'staff')
            ->set('company_name', 'Acme Ltd')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('staff'));
        $this->assertNotTrue($user->hasRole('reseller'));
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/users/create')->assertForbidden();
    }

    public function test_admin_can_edit_another_users_role(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create();

        Livewire::test(UserForm::class, ['user' => $user])
            ->set('name', 'Promoted')
            ->set('role', 'admin')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_admin_cannot_remove_own_admin_role(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Livewire::test(UserForm::class, ['user' => $admin])
            ->set('role', 'reseller')
            ->call('save')
            ->assertHasErrors(['role']);

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Livewire::test(UserIndex::class)
            ->call('confirmDelete', $admin->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $this->actingAs($this->admin());
        $staff = User::factory()->create();

        Livewire::test(UserIndex::class)
            ->call('confirmDelete', $staff->id)
            ->call('delete')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }
}