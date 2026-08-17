<?php

namespace Database\Factories;

use App\Enums\PanelType;
use App\Models\Panel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Panel>
 */
class PanelFactory extends Factory
{
    protected $model = Panel::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company().' '.fake()->randomElement(['cPanel', 'WHM', 'Plesk', 'Server']),
            'type' => fake()->randomElement(PanelType::cases()),
            'host' => fake()->optional()->domainName(),
            'ip_address' => fake()->optional()->ipv4(),
            'client_limit' => fake()->randomElement([0, 10, 50, 100, 500]),
            'username' => fake()->optional()->userName(),
            'login_url' => fake()->optional()->url(),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function cpanel(): static
    {
        return $this->state(fn () => ['type' => PanelType::Cpanel]);
    }

    public function other(): static
    {
        return $this->state(fn () => ['type' => PanelType::Other]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}