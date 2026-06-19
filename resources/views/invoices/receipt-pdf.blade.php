<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>領収書 - {{ $invoice->invoice_number }}</title>
    <style>
        @font-face {
            font-family: 'IPAexMincho';
            src: url('file://{{ storage_path('fonts/ipaexm.ttf') }}');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'IPAexMincho';
            src: url('file://{{ storage_path('fonts/ipaexm.ttf') }}');
            font-weight: bold;
            font-style: normal;
        }

        .top-spacer {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 25mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'IPAexMincho', 'DejaVu Sans', serif;
            font-size: 10.5pt;
            color: #222;
            background: #fff;
            line-height: 1.65;
            word-break: break-all;
            overflow-wrap: break-word;
        }

        .page { padding: 0 18mm 20mm 20mm; }

        .doc-header {
            display: table;
            width: 100%;
            border-bottom: 2pt solid #1a1a2e;
            padding-bottom: 8mm;
            margin-bottom: 8mm;
        }
        .doc-header-left  { display: table-cell; vertical-align: top; width: 55%; }
        .doc-header-right { display: table-cell; vertical-align: top; width: 45%; text-align: right; }
        .doc-title {
            font-size: 22pt;
            font-weight: bold;
            color: #1a1a2e;
            letter-spacing: 5pt;
        }
        .doc-number { font-size: 9pt; color: #666; margin-top: 2pt; }
        .doc-meta   { font-size: 9pt; color: #444; line-height: 1.8; }
        .doc-meta strong { color: #222; }

        .section       { margin-bottom: 7mm; }
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1a1a2e;
            background: #f0f2f8;
            padding: 3pt 7pt;
            border-left: 4pt solid #1a1a2e;
            margin-bottom: 3mm;
            letter-spacing: 0.5pt;
        }

        table.dl { width: 100%; border-collapse: collapse; }
        table.dl td {
            padding: 4pt 6pt;
            vertical-align: top;
            border-bottom: 0.5pt solid #e8e8e8;
            font-size: 10pt;
        }
        table.dl td.label {
            width: 32%;
            color: #666;
            font-weight: bold;
            white-space: nowrap;
        }
        table.dl td.value { color: #111; }

        .amount-box {
            border: 1.5pt solid #1a1a2e;
            padding: 8pt 14pt;
            margin-bottom: 7mm;
            text-align: center;
        }
        .amount-label { font-size: 9pt; color: #666; margin-bottom: 2pt; }
        .amount-value { font-size: 20pt; font-weight: bold; color: #1a1a2e; letter-spacing: 2pt; }
        .amount-note  { font-size: 8pt; color: #888; margin-top: 3pt; }

        .stamp-area {
            display: table;
            width: 100%;
            margin-top: 10mm;
        }
        .stamp-left  { display: table-cell; vertical-align: top; width: 60%; }
        .stamp-right { display: table-cell; vertical-align: top; width: 40%; text-align: right; }
        .stamp-box {
            display: inline-block;
            width: 25mm;
            height: 25mm;
            border: 0.5pt solid #ccc;
            text-align: center;
            vertical-align: middle;
            font-size: 8pt;
            color: #bbb;
            line-height: 25mm;
        }

        .footer {
            position: fixed;
            bottom: 8mm;
            left: 20mm;
            right: 18mm;
            border-top: 0.5pt solid #ccc;
            padding-top: 3pt;
            font-size: 7.5pt;
            color: #bbb;
        }
        .footer table { width: 100%; border-collapse: collapse; }
        .footer td { padding: 0; vertical-align: top; }
        .footer .f-left  { text-align: left; }
        .footer .f-right { text-align: right; }
        .page-num:before { content: "- " counter(page) " -"; }
        .receipt-statement {
            margin-bottom: 8mm;
            padding: 6pt 10pt;
            background: #fafafa;
            border: 0.5pt solid #e0e0e0;
        }
        .tadashi   { font-size: 10pt; color: #444; margin-bottom: 4pt; }
        .ryoushu   { font-size: 11pt; font-weight: bold; color: #1a1a2e; }

        .issuer-block  { text-align: right; margin-top: 8mm; }
        .issuer-name   { font-size: 11pt; font-weight: bold; color: #1a1a2e; }
        .issuer-person { font-size: 10pt; color: #444; margin-top: 2pt; }

        .generated-at {
            font-size: 7.5pt;
            color: #bbb;
            text-align: right;
            margin-top: 6mm;
        }
    </style>
</head>
<body>
    <div class="top-spacer"></div>

    <div class="footer">
        <table><tr>
            <td class="f-left"><span class="page-num"></span></td>
            <td class="f-right">{{ $invoice->invoice_number }} &nbsp;|&nbsp; 発行日：{{ now()->format('Y年m月d日') }}</td>
        </tr></table>
    </div>

    <div class="page">

        {{-- Document header --}}
        <div class="doc-header">
            <div class="doc-header-left">
                <div class="doc-title">領 収 書</div>
                <div class="doc-number">{{ $invoice->invoice_number }}</div>
            </div>
            <div class="doc-header-right">
                <div class="doc-meta">
                    <strong>発行日</strong>&nbsp;&nbsp;{{ now()->format('Y年m月d日') }}<br>
                    <strong>入金日</strong>&nbsp;&nbsp;
                    {{ $invoice->payments->sortByDesc('paid_at')->first()?->paid_at->format('Y年m月d日') ?? '—' }}
                </div>
            </div>
        </div>

        {{-- Recipient --}}
        <div class="section">
            <div class="section-title">宛先</div>
            <table class="dl">
                <tr>
                    <td class="label">会社名</td>
                    <td class="value">{{ $invoice->project->company->company_name }}</td>
                </tr>
                @if ($invoice->project->company->contact_name)
                    <tr>
                        <td class="label">担当者名</td>
                        <td class="value">{{ $invoice->project->company->contact_name }}</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- Amount --}}
        <div class="amount-box">
            <div class="amount-label">領収金額（税込）</div>
            <div class="amount-value">{{ fmt_amount($invoice->total_amount) }}</div>
            <div class="amount-note">
                内訳：税抜 {{ fmt_amount($invoice->amount) }} ／ 消費税（10%）{{ fmt_amount($invoice->tax_amount) }}
            </div>
        </div>

        {{-- Receipt statement --}}
        <div class="receipt-statement">
            <div class="tadashi">但し　{{ $invoice->receipt_description ?: $invoice->title }}　として</div>
            <div class="ryoushu">上記の金額を正に領収いたしました。</div>
        </div>

        {{-- Issuer --}}
        <div class="issuer-block">
            <div class="issuer-name">{{ config('app.operator_company') }}</div>
            <div class="issuer-person">{{ config('app.operator_name') }}</div>
        </div>

        <div class="generated-at">発行日時：{{ now()->format('Y年m月d日 H:i') }}</div>

    </div>
</body>
</html>
