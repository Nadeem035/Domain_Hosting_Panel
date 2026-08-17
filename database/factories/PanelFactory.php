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
            'name' => fake()->company().' '.fake()->randomElement(['cPanel', 'WHM', 'Registrar', 'Server']),
            'type' => fake()->randomElement(PanelType::cases()),
            'login_url' => fake()->optional()->url(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function hosting(): static
    {
        return $this->state(fn () => ['type' => PanelType::Hosting]);
    }

    public function domain(): static
    {
        return $this->state(fn () => ['type' => PanelType::Domain]);
    }
}