<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-xs text-gray-500 mb-1">
                    <a href="{{ route('companies.show', $contract->project->company) }}"
                       class="hover:text-indigo-600">{{ $contract->project->company->company_name }}</a>
                    <span class="mx-1">›</span>
                    <a href="{{ route('projects.show', $contract->project) }}"
                       class="hover:text-indigo-600">{{ $contract->project->title }}</a>
                    <span class="mx-1">›</span>契約
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $contract->contract_number }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                {{-- PDF Generation --}}
                <form method="POST" action="{{ route('contracts.pdf.store', $contract) }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700">
                        PDF生成
                    </button>
                </form>
                <a href="{{ route('contracts.edit', $contract) }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    編集
                </a>
                <a href="{{ route('contracts.index') }}"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    一覧へ戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Contract Info --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">契約情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">契約番号</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $contract->contract_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">契約種別</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $contract->contract_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">ステータス</dt>
                            <dd class="mt-1">
                                <x-status-badge :status="$contract->status" type="contract" />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">締結日</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $contract->signed_at?->format('Y年m月d日') ?? '—' }}
                            </dd>
                        </div>
                        @if ($contract->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">備考</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $contract->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Related Info --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">関連情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('companies.show', $contract->project->company) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $contract->project->company->company_name }}
                                </a>
                                @if ($contract->project->company->contact_name)
                                    <span class="text-gray-400 ml-1">
                                        （{{ $contract->project->company->contact_name }}）
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">案件</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('projects.show', $contract->project) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $contract->project->title }}
                                </a>
                                <span class="ml-2">
                                    <x-status-badge :status="$contract->project->status" type="project" />
                                </span>
                            </dd>
                        </div>
                        @if ($contract->project->company->email)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客メール</dt>
                                <dd class="mt-1 text-sm">
                                    <a href="mailto:{{ $contract->project->company->email }}"
                                       class="text-indigo-600 hover:underline">
                                        {{ $contract->project->company->email }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if ($contract->project->company->phone)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客電話</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $contract->project->company->phone }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Contract Files --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        契約ファイル
                        <span class="ml-2 text-gray-400 font-normal normal-case">
                            {{ $contract->files->count() }} 件
                        </span>
                    </h3>
                    <form method="POST" action="{{ route('contracts.pdf.store', $contract) }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                            + PDFを生成
                        </button>
                    </form>
                </div>

                @if ($contract->files->isEmpty())
                    <div class="px-6 py-8 text-center text-sm text-gray-400">
                        PDFが生成されていません。「PDF生成」ボタンから作成してください。
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ファイル名</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">生成日時</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SHA256</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($contract->files as $file)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                        {{ $file->file_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $file->created_at->format('Y/m/d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400 font-mono truncate max-w-xs" title="{{ $file->file_hash }}">
                                        {{ $file->file_hash ? substr($file->file_hash, 0, 16) . '…' : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('contracts.files.download', [$contract, $file]) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            ダウンロード
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
