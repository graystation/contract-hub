@component('mail::message')
# 契約締結のお知らせ

{{ $contract->signer_name }} 様

以下の契約について、電子契約への同意が完了しました。
本契約書のPDFを添付しておりますので、ご確認・保管をお願いいたします。

---

@component('mail::panel')
**契約番号：** {{ $contract->contract_number }}
**契約種別：** {{ $contract->contract_type }}
**締結日時：** {{ $contract->signed_at?->format('Y年m月d日 H:i') ?? '—' }}
@endcomponent

ご不明な点がございましたら、担当者までお問い合わせください。

{{ config('app.name') }}
@endcomponent
