@php
$typeLabels   = ['advertisement' => '広告掲載', 'consulting' => 'コンサルティング', 'other' => 'その他'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-xs text-gray-500 mb-1">
                    <a href="{{ route('companies.show', $project->company) }}"
                       class="hover:text-indigo-600">{{ $project->company->company_name }}</a>
                    <span class="mx-1">›</span>案件
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->title }}</h2>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('projects.edit', $project) }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    編集
                </a>
                <a href="{{ route('projects.index') }}"
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

            {{-- Project Info --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">案件情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('companies.show', $project->company) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $project->company->company_name }}
                                </a>
                                @if ($project->company->contact_name)
                                    <span class="text-gray-400 ml-1">（{{ $project->company->contact_name }}）</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">種別</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $typeLabels[$project->type] ?? $project->type }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">ステータス</dt>
                            <dd class="mt-1">
                                <x-status-badge :status="$project->status" type="project" />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">期間</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if ($project->started_at)
                                    {{ $project->started_at->format('Y/m/d') }}
                                    @if ($project->ended_at)
                                        〜 {{ $project->ended_at->format('Y/m/d') }}
                                    @endif
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        @if ($project->description)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">説明</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $project->description }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Contracts --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        関連契約
                        <span class="ml-2 text-gray-400 font-normal normal-case">{{ $project->contracts->count() }} 件</span>
                    </h3>
                    <a href="{{ route('contracts.create') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                        + 契約を追加
                    </a>
                </div>

                @if ($project->contracts->isEmpty())
                    <div class="px-6 py-8 text-center text-sm text-gray-400">
                        契約が登録されていません。
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">契約番号</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">契約種別</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">締結日</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($project->contracts as $contract)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('contracts.show', $contract) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                                            {{ $contract->contract_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $contract->contract_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-status-badge :status="$contract->status" type="contract" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $contract->signed_at?->format('Y/m/d') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('contracts.edit', $contract) }}"
                                           class="text-indigo-600 hover:text-indigo-900">編集</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Invoices --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        請求一覧
                        <span class="ml-2 text-gray-400 font-normal normal-case">{{ $invoices->count() }} 件</span>
                    </h3>
                    <a href="{{ route('invoices.create') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                        + 請求を追加
                    </a>
                </div>

                @if ($invoices->isEmpty())
                    <div class="px-6 py-6 text-center text-sm text-gray-400">請求書が登録されていません。</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">請求番号</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイトル</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">税込合計</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">入金済</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($invoices as $inv)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $inv) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">{{ $inv->title }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                        {{ fmt_amount($inv->total_amount) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-right text-green-700">
                                        {{ fmt_amount($inv->paid_amount) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <x-invoice-status :invoice="$inv" />
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
