<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceRenewal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRenewal>
 */
class ServiceRenewalFactory extends Factory
{
    protected $model = ServiceRenewal::class;

    public function definition(): array
    {
        $previousExpiry = fake()->dateTimeBetween('-2 years', '-1 month');

        return [
            'service_id' => Service::factory(),
            'renewed_on' => fake()->dateTimeBetween($previousExpiry, 'now')->format('Y-m-d'),
            'previous_expiry_date' => $previousExpiry->format('Y-m-d'),
            'new_expiry_date' => fake()->dateTimeBetween('now', '+11 months')->format('Y-m-d'),
            'company_price' => fake()->randomFloat(2, 5, 60),
            'client_price' => fn (array $attrs) => round($attrs['company_price'] * 1.6, 2),
            'payment_received' => fake()->boolean(80),
            'payment_received_date' => fn (array $attrs) => $attrs['payment_received'] ? $attrs['renewed_on'] : null,
            'invoice_number' => fn () => 'INV-'.fake()->unique()->numberBetween(1000, 99999),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function unpaid(): static
    {
        return $this->state(fn () => [
            'payment_received' => false,
            'payment_received_date' => null,
        ]);
    }
}