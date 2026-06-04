<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    private static int $sequence = 1;

    public function definition(): array
    {
        $number = 'CTR-TEST-' . str_pad(self::$sequence++, 4, '0', STR_PAD_LEFT);

        return [
            'project_id'      => Project::factory(),
            'contract_number' => $number,
            'contract_type'   => '業務委託契約',
            'status'          => $this->faker->randomElement(Contract::STATUSES),
            'signed_at'       => null,
            'notes'           => null,
        ];
    }
}
