<!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>契約書 - {{ $contract->contract_number }}</title>
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'IPAexMincho', 'DejaVu Sans', serif;
            font-size: 11pt;
            color: #222;
            background: #fff;
            line-height: 1.7;
            word-break: break-all;
            overflow-wrap: break-word;
        }

        .page {
            padding: 30mm 20mm 25mm 25mm;
        }

        /* Header */
        .doc-header {
            text-align: center;
            margin-bottom: 14mm;
            border-bottom: 2pt solid #1a1a2e;
            padding-bottom: 8mm;
        }

        .doc-title {
            font-size: 20pt;
            font-weight: bold;
            color: #1a1a2e;
            letter-spacing: 6pt;
            margin-bottom: 10pt;
        }

        .doc-number {
            font-size: 10pt;
            color: #555;
            letter-spacing: 1pt;
        }

        /* Section */
        .section {
            margin-bottom: 10mm;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1a1a2e;
            background: #f0f2f8;
            padding: 4pt 8pt;
            border-left: 4pt solid #1a1a2e;
            margin-bottom: 4mm;
            letter-spacing: 1pt;
            page-break-after: avoid;
        }

        /* Definition list table */
        table.dl {
            width: 100%;
            border-collapse: collapse;
        }

        table.dl td {
            padding: 5pt 8pt;
            vertical-align: top;
            border-bottom: 0.5pt solid #e0e0e0;
            font-size: 10.5pt;
        }

        table.dl td.label {
            width: 35%;
            color: #555;
            font-weight: bold;
            white-space: nowrap;
        }

        table.dl td.value {
            color: #111;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 2pt 8pt;
            border-radius: 3pt;
            font-size: 9pt;
            font-weight: bold;
        }

        .status-draft     { background: #e5e7eb; color: #374151; }
        .status-sent      { background: #fef3c7; color: #92400e; }
        .status-signed    { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* Contract body (rich text from Tiptap) */
        .contract-body { font-size: 10pt; line-height: 1.8; }
        .contract-body h2 { font-size: 12pt; font-weight: bold; margin: 10pt 0 4pt; border-bottom: 0.5pt solid #ccc; padding-bottom: 2pt; page-break-after: avoid; }
        .contract-body h3 { font-size: 10.5pt; font-weight: bold; margin: 8pt 0 3pt; page-break-after: avoid; }
        .contract-body p  { margin: 0 0 6pt; }
        .contract-body ul, .contract-body ol { margin: 4pt 0 6pt 16pt; }
        .contract-body li { margin-bottom: 2pt; }
        .contract-body strong { font-weight: bold; }
        .contract-body u { text-decoration: underline; }

        /* Notes box */
        .notes-box {
            background: #fafafa;
            border: 0.5pt solid #ddd;
            padding: 6pt 10pt;
            font-size: 10pt;
            min-height: 20mm;
            white-space: pre-wrap;
        }

        /* Footer — repeated on every page via position:fixed */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 25mm;
            right: 20mm;
            border-top: 0.5pt solid #ccc;
            padding-top: 4pt;
            font-size: 8pt;
            color: #aaa;
        }
        .footer table { width: 100%; border-collapse: collapse; }
        .footer td { padding: 0; vertical-align: top; }
        .footer .f-left  { text-align: left; }
        .footer .f-right { text-align: right; }
        /* counter(pages) is unreliable in dompdf; show only current page number */
        .page-num:before { content: "- " counter(page) " -"; }

        .generated-at {
            font-size: 8pt;
            color: #999;
            text-align: right;
            margin-top: 6mm;
        }
    </style>
</head>
<body>

    <div class="footer">
        <table><tr>
            <td class="f-left"><span class="page-num"></span></td>
            <td class="f-right">{{ $contract->contract_number }} &nbsp;|&nbsp; {{ now()->format('Y年m月d日') }} 生成</td>
        </tr></table>
    </div>

    <div class="page">

        {{-- Document Header --}}
        <div class="doc-header">
            <div class="doc-title">契 約 書</div>
            <div class="doc-number">{{ $contract->contract_number }}</div>
        </div>

        {{-- Customer Information --}}
        <div class="section">
            <div class="section-title">顧客情報</div>
            <table class="dl">
                <tr>
                    <td class="label">会社名</td>
                    <td class="value">{{ $contract->project->company->company_name }}</td>
                </tr>
                @if ($contract->project->company->contact_name)
                <tr>
                    <td class="label">担当者名</td>
                    <td class="value">{{ $contract->project->company->contact_name }}</td>
                </tr>
                @endif
                @if ($contract->project->company->email)
                <tr>
                    <td class="label">メールアドレス</td>
                    <td class="value">{{ $contract->project->company->email }}</td>
                </tr>
                @endif
                @if ($contract->project->company->phone)
                <tr>
                    <td class="label">電話番号</td>
                    <td class="value">{{ $contract->project->company->phone }}</td>
                </tr>
                @endif
                @if ($contract->project->company->address)
                <tr>
                    <td class="label">住所</td>
                    <td class="value">{{ $contract->project->company->address }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Project Information --}}
        <div class="section">
            <div class="section-title">案件情報</div>
            <table class="dl">
                <tr>
                    <td class="label">案件名</td>
                    <td class="value">{{ $contract->project->title }}</td>
                </tr>
            </table>
        </div>

        {{-- Contract Details --}}
        <div class="section">
            <div class="section-title">契約内容</div>
            <table class="dl">
                <tr>
                    <td class="label">契約番号</td>
                    <td class="value">{{ $contract->contract_number }}</td>
                </tr>
                <tr>
                    <td class="label">契約種別</td>
                    <td class="value">{{ $contract->contract_type }}</td>
                </tr>
                <tr>
                    <td class="label">契約状態</td>
                    <td class="value">
                        @php
                            $statusLabels = ['draft' => '下書き', 'sent' => '送付済', 'signed' => '締結済', 'cancelled' => 'キャンセル'];
                            $statusClass  = 'status-' . $contract->status;
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusLabels[$contract->status] ?? $contract->status }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">締結日</td>
                    <td class="value">
                        {{ $contract->signed_at ? $contract->signed_at->format('Y年m月d日') : '—' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Contract body --}}
        @if ($contract->body)
        <div class="section">
            <div class="section-title">契約書本文</div>
            <div class="contract-body">{!! $contract->body !!}</div>
        </div>
        @endif

        {{-- Notes --}}
        @if ($contract->notes)
        <div class="section">
            <div class="section-title">備考</div>
            <div class="notes-box">{{ $contract->notes }}</div>
        </div>
        @endif

        {{-- Signer Info (only when signed) --}}
        @if ($contract->status === 'signed' && $contract->signer_name)
        <div class="section">
            <div class="section-title">電子同意情報</div>
            <table class="dl">
                <tr>
                    <td class="label">同意者氏名</td>
                    <td class="value">{{ $contract->signer_name }}</td>
                </tr>
                <tr>
                    <td class="label">同意者メール</td>
                    <td class="value">{{ $contract->signer_email }}</td>
                </tr>
                <tr>
                    <td class="label">同意日時</td>
                    <td class="value">{{ $contract->signed_at ? $contract->signed_at->format('Y年m月d日 H:i:s') : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">同意者IPアドレス</td>
                    <td class="value">{{ $contract->signer_ip_address ?? '—' }}</td>
                </tr>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        <div class="generated-at">
            生成日時：{{ now()->format('Y年m月d日 H:i') }}
        </div>

    </div>
</body>
</html>
