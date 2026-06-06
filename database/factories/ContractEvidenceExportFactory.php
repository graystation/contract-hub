<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractEvidenceExportFactory extends Factory
{
    public function definition(): array
    {
        $timestamp = now()->format('YmdHis');

        return [
            'contract_id'  => Contract::factory(),
            'file_name'    => "evidence_CTR-TEST-0001_{$timestamp}.zip",
            'file_path'    => "evidence_CTR-TEST-0001_{$timestamp}.zip",
            'file_hash'    => null,
            'generated_at' => now(),
        ];
    }
}
