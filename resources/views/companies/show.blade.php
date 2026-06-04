<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $company->company_name }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('companies.edit', $company) }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    編集
                </a>
                <a href="{{ route('companies.index') }}"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    一覧へ戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Company Info --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">基本情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">会社名</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $company->company_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">担当者名</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $company->contact_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">メールアドレス</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if ($company->email)
                                    <a href="mailto:{{ $company->email }}" class="text-indigo-600 hover:underline">
                                        {{ $company->email }}
                                    </a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">電話番号</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $company->phone ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">住所</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $company->address ?? '—' }}</dd>
                        </div>
                        @if ($company->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">備考</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $company->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Invoice summary --}}
            @if ($invoiceSummary['count'] > 0)
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">請求サマリー</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-6 text-center">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">請求合計</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">{{ fmt_amount($invoiceSummary['total']) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">入金済</div>
                            <div class="mt-1 text-lg font-bold text-green-700">{{ fmt_amount($invoiceSummary['paid']) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">未入金</div>
                            <div class="mt-1 text-lg font-bold {{ $invoiceSummary['unpaid'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                {{ fmt_amount($invoiceSummary['unpaid']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Projects --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        関連案件
                        <span class="ml-2 text-gray-400 font-normal normal-case">{{ $company->projects->count() }} 件</span>
                    </h3>
                    <a href="{{ route('projects.create') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                        + 案件を追加
                    </a>
                </div>

                @if ($company->projects->isEmpty())
                    <div class="px-6 py-8 text-center text-sm text-gray-400">
                        案件が登録されていません。
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">案件名</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">種別</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開始日</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">契約数</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($company->projects as $project)
                                @php
                                    $typeLabels = ['advertisement' => '広告掲載', 'consulting' => 'コンサルティング', 'other' => 'その他'];
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('projects.show', $project) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                                            {{ $project->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $typeLabels[$project->type] ?? $project->type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-status-badge :status="$project->status" type="project" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $project->started_at?->format('Y/m/d') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                        {{ $project->contracts_count }}
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
