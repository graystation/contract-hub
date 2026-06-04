<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $company->company_name }}</h2>
            <div class="space-x-2">
                <a href="{{ route('companies.edit', $company) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                    編集
                </a>
                <a href="{{ route('companies.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
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
                        <dt class="text-sm font-medium text-gray-500">会社名</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->company_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">担当者名</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->contact_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">メール</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">電話</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->phone ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">住所</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->address ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">備考</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $company->notes ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">関連案件</h3>
                    <a href="{{ route('projects.create') }}" class="text-sm text-indigo-600 hover:text-indigo-900">+ 案件を追加</a>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">案件名</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">種別</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">開始日</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($company->projects as $project)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $project->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $project->type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $project->status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $project->started_at?->format('Y/m/d') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">案件がありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
