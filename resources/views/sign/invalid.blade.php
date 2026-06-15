<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>無効なURL</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8 text-center">

                    @php
                        $messages = [
                            'not_found'  => ['icon' => '🔍', 'title' => 'URLが見つかりません', 'body' => '指定された同意URLは存在しません。URLをご確認ください。'],
                            'expired'    => ['icon' => '⏰', 'title' => '同意URLの有効期限が切れています', 'body' => 'この同意URLの有効期限が切れています。担当者にご連絡のうえ、URLを再発行していただいてください。'],
                            'signed'     => ['icon' => '✅', 'title' => 'この契約はすでに締結済みです', 'body' => 'この契約への同意はすでに完了しています。'],
                            'signed_expired' => ['icon' => '✅', 'title' => 'この契約はすでに締結済みです', 'body' => 'この契約への同意は完了していますが、PDFのダウンロード期限（締結から14日間）を過ぎたため、このURLからのダウンロードはできません。PDFが必要な場合は担当者にお問い合わせください。'],
                            'cancelled'  => ['icon' => '❌', 'title' => 'この契約はキャンセルされています', 'body' => 'この契約はキャンセルされています。詳細は担当者にお問い合わせください。'],
                        ];
                        $msg = $messages[$reason] ?? $messages['not_found'];
                    @endphp

                    <div class="text-4xl mb-4">{{ $msg['icon'] }}</div>
                    <h1 class="text-xl font-bold text-gray-900 mb-2">{{ $msg['title'] }}</h1>
                    <p class="text-sm text-gray-500 mb-4">{{ $msg['body'] }}</p>

                    @if (isset($contract) && $reason === 'signed')
                        <div class="mt-4 border border-gray-100 rounded-md px-4 py-3 text-left text-xs text-gray-500">
                            <div>契約番号：{{ $contract->contract_number }}</div>
                            <div>締結日時：{{ $contract->signed_at?->format('Y年m月d日 H:i') ?? '—' }}</div>
                        </div>

                        <a href="{{ route('sign.contracts.pdf', $contract->sign_token) }}"
                           class="block w-full mt-4 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            契約書PDFをダウンロード
                        </a>
                    @endif

                    @if (isset($contract) && $reason === 'expired')
                        <div class="mt-4 border border-gray-100 rounded-md px-4 py-3 text-left text-xs text-gray-500">
                            <div>契約番号：{{ $contract->contract_number }}</div>
                            <div>有効期限：{{ $contract->sign_token_expires_at?->format('Y年m月d日 H:i') ?? '—' }}</div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

</body>
</html>
