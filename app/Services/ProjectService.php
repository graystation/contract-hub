<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Project::with('company')
            ->withCount('contracts')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
