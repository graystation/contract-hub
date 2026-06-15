<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>同意完了 — {{ $contract->contract_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8 text-center">

                    {{-- Success icon --}}
                    <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <h1 class="text-xl font-bold text-gray-900 mb-2">同意が完了しました</h1>
                    <p class="text-sm text-gray-500 mb-6">
                        契約への同意が正常に記録されました。
                    </p>

                    <dl class="text-left border border-gray-100 rounded-md divide-y divide-gray-100 mb-6">
                        <div class="px-4 py-3 grid grid-cols-2 gap-2">
                            <dt class="text-xs font-medium text-gray-500">契約番号</dt>
                            <dd class="text-xs text-gray-900 font-medium">{{ $contract->contract_number }}</dd>
                        </div>
                        <div class="px-4 py-3 grid grid-cols-2 gap-2">
                            <dt class="text-xs font-medium text-gray-500">会社名</dt>
                            <dd class="text-xs text-gray-900">{{ $contract->project->company->company_name }} 様</dd>
                        </div>
                        <div class="px-4 py-3 grid grid-cols-2 gap-2">
                            <dt class="text-xs font-medium text-gray-500">同意者氏名</dt>
                            <dd class="text-xs text-gray-900">{{ $contract->signer_name }} 様</dd>
                        </div>
                        <div class="px-4 py-3 grid grid-cols-2 gap-2">
                            <dt class="text-xs font-medium text-gray-500">同意者メール</dt>
                            <dd class="text-xs text-gray-900">{{ $contract->signer_email }}</dd>
                        </div>
                        <div class="px-4 py-3 grid grid-cols-2 gap-2">
                            <dt class="text-xs font-medium text-gray-500">同意日時</dt>
                            <dd class="text-xs text-gray-900">{{ $signedAt->format('Y年m月d日 H:i') }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('sign.contracts.pdf', $contract->sign_token) }}"
                       class="block w-full px-4 py-2 mb-4 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        契約書PDFをダウンロード
                    </a>

                    <p class="text-xs text-gray-400">
                        このページを閉じても問題ありません。<br>
                        同意内容はシステムに記録されています。
                    </p>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
