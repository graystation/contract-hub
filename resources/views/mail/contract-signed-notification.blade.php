@component('mail::message')
# 契約締結が完了しました

以下の契約について、外部ユーザーによる電子同意が完了しました。

---

@component('mail::panel')
**契約番号：** {{ $contract->contract_number }}
**顧客名：** {{ $company->company_name }}
**案件名：** {{ $project->title }}
**契約種別：** {{ $contract->contract_type }}
@endcomponent

### 同意者情報

| 項目 | 内容 |
|---|---|
| 同意者氏名 | {{ $contract->signer_name }} |
| 同意者メール | {{ $contract->signer_email }} |
| 同意日時 | {{ $contract->signed_at?->format('Y年m月d日 H:i:s') ?? '—' }} |
| IPアドレス | {{ $contract->signer_ip_address ?? '—' }} |
| User-Agent | {{ $contract->signer_user_agent ?? '—' }} |

@component('mail::button', ['url' => $adminUrl])
契約詳細を確認する
@endcomponent

{{ config('app.name') }}
@endcomponent
