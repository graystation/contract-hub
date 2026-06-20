<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>契約内容の確認・同意 — {{ $contract->contract_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <div class="min-h-screen py-12 px-4">
        <div class="max-w-2xl mx-auto space-y-6">

            {{-- Header --}}
            <div class="text-center mb-8">
                <p class="text-sm text-gray-500 mb-1">電子契約確認</p>
                <h1 class="text-2xl font-bold text-gray-900">契約内容のご確認</h1>
                <p class="mt-2 text-sm text-gray-500">
                    以下の契約内容をご確認のうえ、同意いただく場合はフォームにご記入ください。
                </p>
            </div>

            {{-- Contract details --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">契約情報</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">契約番号</dt>
                            <dd class="col-span-2 text-sm text-gray-900 font-medium">{{ $contract->contract_number }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">契約種別</dt>
                            <dd class="col-span-2 text-sm text-gray-900">{{ $contract->contract_type }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">顧客名</dt>
                            <dd class="col-span-2 text-sm text-gray-900">{{ $contract->project->company->company_name }} 様</dd>
                        </div>
                        @if ($contract->project->company->contact_name)
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">担当者名</dt>
                            <dd class="col-span-2 text-sm text-gray-900">{{ $contract->project->company->contact_name }} 様</dd>
                        </div>
                        @endif
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">案件名</dt>
                            <dd class="col-span-2 text-sm text-gray-900">{{ $contract->project->title }}</dd>
                        </div>
                        @if ($contract->notes)
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">備考・契約内容</dt>
                            <dd class="col-span-2 text-sm text-gray-900 whitespace-pre-line">{{ $contract->notes }}</dd>
                        </div>
                        @endif
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">同意URLの有効期限</dt>
                            <dd class="col-span-2 text-sm text-gray-900">
                                {{ $contract->sign_token_expires_at->format('Y年m月d日 H:i') }} まで
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Contract body — the actual terms the signer is agreeing to --}}
            @if ($contract->body)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">契約書本文</h2>
                </div>
                <div class="px-6 py-5 prose prose-sm max-w-none">
                    {!! $contract->body !!}
                </div>
            </div>
            @endif

            {{-- Consent form --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">同意者情報の入力</h2>
                </div>
                <div class="p-6">

                    @if ($errors->any())
                        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-300 text-red-700 rounded-md text-sm">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sign.contracts.store', $token) }}">
                        @csrf
                        <div class="space-y-5">
                            <div>
                                <label for="signer_name" class="block text-sm font-medium text-gray-700">
                                    契約書記載氏名 <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="signer_name"
                                       name="signer_name"
                                       value="{{ old('signer_name') }}"
                                       placeholder="山田 太郎"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('signer_name') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-400">契約書PDFに記載されるお名前です。</p>
                            </div>

                            <div>
                                <label for="signer_address" class="block text-sm font-medium text-gray-700">
                                    住所 <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="signer_address"
                                       name="signer_address"
                                       value="{{ old('signer_address') }}"
                                       placeholder="東京都千代田区〇〇1-2-3"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('signer_address') border-red-500 @enderror">
                            </div>

                            <div>
                                <label for="signer_email" class="block text-sm font-medium text-gray-700">
                                    メールアドレス <span class="text-red-500">*</span>
                                </label>
                                <input type="email"
                                       id="signer_email"
                                       name="signer_email"
                                       value="{{ old('signer_email', $contract->project->company->email ?? '') }}"
                                       placeholder="example@example.com"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('signer_email') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-400">
                                    契約締結完了後、このアドレスに契約書PDFをお送りします。
                                </p>
                            </div>

                            <div>
                                <label for="signer_email_confirmation" class="block text-sm font-medium text-gray-700">
                                    メールアドレス（確認用） <span class="text-red-500">*</span>
                                </label>
                                <input type="email"
                                       id="signer_email_confirmation"
                                       name="signer_email_confirmation"
                                       value="{{ old('signer_email_confirmation', $contract->project->company->email ?? '') }}"
                                       placeholder="example@example.com"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('signer_email_confirmation') border-red-500 @enderror">
                            </div>

                            <div class="pt-2 border-t border-gray-100">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox"
                                           name="agreed"
                                           value="1"
                                           {{ old('agreed') ? 'checked' : '' }}
                                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 @error('agreed') border-red-500 @enderror">
                                    <span class="text-sm text-gray-700">
                                        上記の契約内容（契約番号：{{ $contract->contract_number }}）を確認し、同意します。
                                    </span>
                                </label>
                                @error('agreed')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-4">
                                <button type="submit"
                                        class="w-full py-3 px-4 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                                    同意して契約を締結する
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <p class="text-center text-xs text-gray-400">
                このページのURLは第三者と共有しないでください。
            </p>

        </div>
    </div>

</body>
</html>
