<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id'  => Company::factory(),
            'title'       => $this->faker->sentence(3),
            'type'        => $this->faker->randomElement(Project::TYPES),
            'description' => $this->faker->paragraph(),
            'status'      => $this->faker->randomElement(Project::STATUSES),
            'started_at'  => now()->subMonths(3),
            'ended_at'    => null,
        ];
    }
}
