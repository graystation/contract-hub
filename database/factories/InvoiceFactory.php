<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    private static int $sequence = 1;

    public function definition(): array
    {
        $amount    = $this->faker->numberBetween(50000, 500000);
        $taxAmount = (int) round($amount * 0.10);

        return [
            'project_id'     => Project::factory(),
            'invoice_number' => 'INV-TEST-' . str_pad(self::$sequence++, 4, '0', STR_PAD_LEFT),
            'title'          => '業務委託費用',
            'amount'         => $amount,
            'tax_amount'     => $taxAmount,
            'total_amount'   => $amount + $taxAmount,
            'issued_at'      => now()->subDays(30),
            'due_date'       => now()->addDays(30),
            'status'         => 'issued',
            'notes'          => null,
        ];
    }
}
