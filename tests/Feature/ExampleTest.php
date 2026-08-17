<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_authenticated_user_reaches_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk();
    }
}