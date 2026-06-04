<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signer_name'  => ['required', 'string', 'max:255'],
            'signer_email' => ['required', 'email', 'max:255'],
            'agreed'       => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'signer_name.required'  => 'お名前を入力してください。',
            'signer_email.required' => 'メールアドレスを入力してください。',
            'signer_email.email'    => '正しいメールアドレスを入力してください。',
            'agreed.required'       => '契約内容への同意が必要です。',
            'agreed.accepted'       => '契約内容への同意が必要です。',
        ];
    }
}
