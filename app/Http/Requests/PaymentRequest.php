<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'  => ['required', 'numeric', 'min:1'],
            'paid_at' => ['required', 'date'],
            'method'  => ['required', Rule::in(Payment::METHODS)],
            'memo'    => ['nullable', 'string'],
        ];
    }
}
