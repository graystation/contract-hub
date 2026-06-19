<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>請求書 - {{ $invoice->invoice_number }}</title>
    <style>
        /*
         * Japanese font — IPAex明朝 (TrueType, IPA license).
         * Pre-registered in storage/fonts/installed-fonts.json.
         */
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

        /*
         * dompdf only honors @page margin-top on the first page, not on
         * continuation pages. A fixed-position spacer (same mechanism as
         * the footer) reserves top space on every page instead.
         */
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

        /* ---- Header ---- */
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

        /* ---- Section ---- */
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

        /* ---- Definition list table ---- */
        table.dl {
            width: 100%;
            border-collapse: collapse;
        }
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

        /* ---- Amount table ---- */
        table.amount {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1mm;
        }
        table.amount th, table.amount td {
            padding: 5pt 8pt;
            font-size: 10pt;
        }
        table.amount thead tr { background: #1a1a2e; color: #fff; }
        table.amount tbody tr:nth-child(even) { background: #f8f8fb; }
        table.amount tfoot tr { border-top: 1.5pt solid #1a1a2e; }
        table.amount .right { text-align: right; }
        table.amount .total-row td { font-weight: bold; font-size: 11pt; }
        table.amount .paid-row td  { color: #166534; }
        table.amount .unpaid-row td { color: #991b1b; font-weight: bold; }
        table.amount .over-row td  { color: #92400e; }

        /* ---- Status badge ---- */
        .badge {
            display: inline-block;
            padding: 2pt 7pt;
            border-radius: 3pt;
            font-size: 8.5pt;
            font-weight: bold;
        }
        .badge-draft     { background: #e5e7eb; color: #374151; }
        .badge-issued    { background: #dbeafe; color: #1e40af; }
        .badge-paid      { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        /* ---- Subject line ---- */
        .subject-line {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a2e;
            border-bottom: 1pt solid #1a1a2e;
            padding-bottom: 4pt;
            margin-bottom: 7mm;
        }
        .subject-label { color: #666; font-weight: normal; }

        /* ---- Notes ---- */
        .notes-box {
            background: #fafafa;
            border: 0.5pt solid #ddd;
            padding: 6pt 10pt;
            font-size: 9.5pt;
            min-height: 15mm;
            white-space: pre-wrap;
        }

        /* ---- Footer — repeated on every page via position:fixed ---- */
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
            <td class="f-right">{{ $invoice->invoice_number }} &nbsp;|&nbsp; 生成日時：{{ now()->format('Y年m月d日 H:i') }}</td>
        </tr></table>
    </div>

    <div class="page">

        {{-- Document header --}}
        <div class="doc-header">
            <div class="doc-header-left">
                <div class="doc-title">請 求 書</div>
                <div class="doc-number">{{ $invoice->invoice_number }}</div>
            </div>
            <div class="doc-header-right">
                <div class="doc-meta">
                    <strong>発行日</strong>&nbsp;&nbsp;
                    {{ $invoice->issued_at ? $invoice->issued_at->format('Y年m月d日') : '—' }}
                    <br>
                    <strong>支払期限</strong>&nbsp;
                    {{ $invoice->due_date ? $invoice->due_date->format('Y年m月d日') : '—' }}
                    <br>
                    @if ($invoice->status === 'paid')
                    <strong>ステータス</strong>&nbsp;
                    <span class="badge badge-paid">入金済</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Billing to --}}
        <div class="section">
            <div class="section-title">請求先</div>
            <table class="dl">
                <tr>
                    <td class="label">会社名</td>
                    <td class="value">{{ $invoice->project->company->company_name }}　御中</td>
                </tr>
                @if ($invoice->project->company->contact_name)
                    <tr>
                        <td class="label">担当者名</td>
                        <td class="value">{{ $invoice->project->company->contact_name }}　様</td>
                    </tr>
                @endif
                @if ($invoice->project->company->address)
                    <tr>
                        <td class="label">住所</td>
                        <td class="value">{{ $invoice->project->company->address }}</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- Subject --}}
        <div class="subject-line">
            <span class="subject-label">件名：</span>{{ $invoice->title }}
        </div>

        {{-- Amount breakdown --}}
        <div class="section">
            <div class="section-title">金額</div>
            <table class="amount">
                <thead>
                    <tr>
                        <th>項目</th>
                        <th class="right">金額</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>税抜金額</td>
                        <td class="right">{{ fmt_amount($invoice->amount) }}</td>
                    </tr>
                    <tr>
                        <td>消費税（10%）</td>
                        <td class="right">{{ fmt_amount($invoice->tax_amount) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>税込合計</td>
                        <td class="right">{{ fmt_amount($invoice->total_amount) }}</td>
                    </tr>
                    @if ($invoice->status === 'paid')
                        <tr class="paid-row">
                            <td>入金済額</td>
                            <td class="right">{{ fmt_amount($invoice->paid_amount) }}</td>
                        </tr>
                        @if ($invoice->is_overpaid)
                            <tr class="over-row">
                                <td>過入金額</td>
                                <td class="right">{{ fmt_amount($invoice->overpaid_amount) }}</td>
                            </tr>
                        @endif
                    @endif
                </tfoot>
            </table>
        </div>

        {{-- Bank transfer info --}}
        @if (config('contract.bank_name'))
            <div class="section">
                <div class="section-title">お振込先</div>
                <table class="dl">
                    <tr>
                        <td class="label">金融機関</td>
                        <td class="value">{{ config('contract.bank_name') }}　{{ config('contract.bank_branch') }}</td>
                    </tr>
                    <tr>
                        <td class="label">口座種別・番号</td>
                        <td class="value">{{ config('contract.bank_account_type') }}　{{ config('contract.bank_account_number') }}</td>
                    </tr>
                    <tr>
                        <td class="label">口座名義</td>
                        <td class="value">{{ config('contract.bank_account_name') }}</td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- Notes --}}
        @if ($invoice->notes)
            <div class="section">
                <div class="section-title">備考</div>
                <div class="notes-box">{{ $invoice->notes }}</div>
            </div>
        @endif

        <div class="generated-at">
            生成日時：{{ now()->format('Y年m月d日 H:i') }}
        </div>

    </div>
</body>
</html>
