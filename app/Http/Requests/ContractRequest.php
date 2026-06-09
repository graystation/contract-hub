<?php

namespace App\Http\Requests;

use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contractId = $this->route('contract')?->id;

        return [
            'project_id'      => ['required', 'exists:projects,id'],
            'template_id'     => ['nullable', 'exists:contract_templates,id'],
            'contract_number' => ['required', 'string', 'max:50', Rule::unique('contracts', 'contract_number')->ignore($contractId)],
            'contract_type'   => ['required', 'string', 'max:255'],
            'signed_at'       => ['nullable', 'date'],
            'status'          => ['required', Rule::in(Contract::STATUSES)],
            'notes'           => ['nullable', 'string'],
            'body'            => ['nullable', 'string'],
        ];
    }
}
