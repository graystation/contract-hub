<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'  => ['required', 'exists:companies,id'],
            'title'       => ['required', 'string', 'max:255'],
            'type'        => ['required', Rule::in(Project::TYPES)],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::in(Project::STATUSES)],
            'started_at'  => ['nullable', 'date'],
            'ended_at'    => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }
}
