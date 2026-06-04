@php
$typeLabels = ['advertisement' => '広告掲載', 'consulting' => 'コンサルティング', 'other' => 'その他'];
$statusLabels = ['draft' => '下書き', 'active' => '進行中', 'completed' => '完了', 'cancelled' => 'キャンセル'];
$statusColors = ['draft' => 'gray', 'active' => 'green', 'completed' => 'blue', 'cancelled' => 'red'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">案件管理</h2>
            <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                新規登録
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">案件名</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">種別</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開始日</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($projects as $project)
                            @php $color = $statusColors[$project->status] ?? 'gray'; @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ $project->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <a href="{{ route('companies.show', $project->company) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $project->company->company_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $typeLabels[$project->type] ?? $project->type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                        {{ $statusLabels[$project->status] ?? $project->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $project->started_at?->format('Y/m/d') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('projects.edit', $project) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>
                                    <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline" onsubmit="return confirm('削除してよろしいですか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">案件が登録されていません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $projects->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
