@component('mail::message')
# 契約内容確認のお願い

{{ $company->company_name }}
@if ($company->contact_name)
{{ $company->contact_name }} 様
@endif

いつもお世話になっております。

下記の契約内容についてご確認をお願いいたします。
内容をご確認のうえ、同意いただける場合はボタンよりご回答ください。

---

@component('mail::panel')
**契約番号：** {{ $contract->contract_number }}
**案件名：** {{ $project->title }}
**契約種別：** {{ $contract->contract_type }}
**有効期限：** {{ $expiresAt->format('Y年m月d日 H:i') }} まで
@endcomponent

@component('mail::button', ['url' => $signUrl])
契約内容を確認して同意する
@endcomponent

上記ボタンが機能しない場合は、以下のURLをブラウザに貼り付けてアクセスしてください。

{{ $signUrl }}

---

このメールに心当たりのない場合は、無視していただいて構いません。

{{ config('app.name') }}
@endcomponent
