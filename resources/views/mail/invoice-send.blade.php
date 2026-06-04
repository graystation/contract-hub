@component('mail::message')
# 請求書のご送付

{{ $company->company_name }}
@if ($company->contact_name)
{{ $company->contact_name }} 様
@endif

いつもお世話になっております。

下記の通り、請求書をお送りします。
ご確認のうえ、お支払い期限までにお振込みいただきますようお願いいたします。

---

@component('mail::panel')
**請求番号：** {{ $invoice->invoice_number }}
**タイトル：** {{ $invoice->title }}
**案件名：** {{ $project->title }}
**税込合計：** {{ fmt_amount($invoice->total_amount) }}
**支払期限：** {{ $invoice->due_date ? $invoice->due_date->format('Y年m月d日') : '未設定' }}
@endcomponent

---

### お振込みのお願い

恐れ入りますが、お支払い期限までに下記のご対応をお願いいたします。

* 添付の請求書PDFをご確認ください。
* 記載の金額をご確認のうえ、期日内にお振込みください。

**添付ファイル：** {{ $invoiceFile->file_name }}

---

ご不明な点がございましたら、お気軽にご連絡ください。

{{ config('app.name') }}
@endcomponent
