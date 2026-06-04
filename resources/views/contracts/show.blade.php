@php
$statusLabels = ['draft' => '下書き', 'sent' => '送付済', 'signed' => '締結済', 'cancelled' => 'キャンセル'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">契約詳細 — {{ $contract->contract_number }}</h2>
            <div class="space-x-2">
                <a href="{{ route('contracts.edit', $contract) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                    編集
                </a>
                <a href="{{ route('contracts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                    一覧へ戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">基本情報</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">契約番号</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $contract->contract_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">契約種別</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $contract->contract_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">顧客</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('companies.show', $contract->project->company) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $contract->project->company->company_name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">案件</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('projects.show', $contract->project) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $contract->project->title }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $statusLabels[$contract->status] ?? $contract->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">締結日</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $contract->signed_at?->format('Y/m/d') ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">備考</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $contract->notes ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
