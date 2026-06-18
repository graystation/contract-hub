<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'  => ['required', 'exists:projects,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
            'title'      => ['required', 'string', 'max:255'],
            'amount'     => ['required', 'numeric', 'min:0'],
            'issued_at'  => ['nullable', 'date'],
            'due_date'   => ['nullable', 'date', 'after_or_equal:issued_at'],
            'status'     => ['required', Rule::in(Invoice::STATUSES)],
            'notes'      => ['nullable', 'string'],
        ];
    }
}
