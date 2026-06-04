<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount'     => $this->faker->numberBetween(10000, 100000),
            'paid_at'    => now()->subDays($this->faker->numberBetween(1, 30)),
            'method'     => $this->faker->randomElement(Payment::METHODS),
            'memo'       => null,
        ];
    }
}
