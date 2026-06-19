@component('mail::message')
# 領収書のご送付

{{ $company->company_name }}
@if ($company->contact_name)
{{ $company->contact_name }} 様
@endif

いつもお世話になっております。

このたびはお支払いいただきありがとうございます。
下記の通り、領収書をお送りします。

---

@component('mail::panel')
**請求番号：** {{ $invoice->invoice_number }}
**タイトル：** {{ $invoice->title }}
**案件名：** {{ $project->title }}
**領収金額（税込）：** {{ fmt_amount($invoice->total_amount) }}
@endcomponent

---

**添付ファイル：** {{ $receiptFile->file_name }}

ご不明な点がございましたら、お気軽にご連絡ください。

{{ config('app.name') }}
@endcomponent
