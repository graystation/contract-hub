@php
$typeLabels = ['advertisement' => '広告掲載', 'consulting' => 'コンサルティング', 'other' => 'その他'];
$statusLabels = ['draft' => '下書き', 'active' => '進行中', 'completed' => '完了', 'cancelled' => 'キャンセル'];
$contractStatusLabels = ['draft' => '下書き', 'sent' => '送付済', 'signed' => '締結済', 'cancelled' => 'キャンセル'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->title }}</h2>
            <div class="space-x-2">
                <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                    編集
                </a>
                <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                    一覧へ戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">基本情報</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">案件名</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">顧客</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('companies.show', $project->company) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $project->company->company_name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">種別</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $typeLabels[$project->type] ?? $project->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $statusLabels[$project->status] ?? $project->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">開始日</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->started_at?->format('Y/m/d') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">終了日</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->ended_at?->format('Y/m/d') ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">説明</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $project->description ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">関連契約</h3>
                    <a href="{{ route('contracts.create') }}" class="text-sm text-indigo-600 hover:text-indigo-900">+ 契約を追加</a>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">契約番号</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">種別</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">締結日</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($project->contracts as $contract)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('contracts.show', $contract) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $contract->contract_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $contract->contract_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $contractStatusLabels[$contract->status] ?? $contract->status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $contract->signed_at?->format('Y/m/d') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">契約がありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
