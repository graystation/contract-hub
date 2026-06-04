@php
$statusLabels = ['draft' => '下書き', 'sent' => '送付済', 'signed' => '締結済', 'cancelled' => 'キャンセル'];
$statusColors = ['draft' => 'gray', 'sent' => 'yellow', 'signed' => 'green', 'cancelled' => 'red'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">契約管理</h2>
            <a href="{{ route('contracts.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">契約番号</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客 / 案件</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">契約種別</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">締結日</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($contracts as $contract)
                            @php $color = $statusColors[$contract->status] ?? 'gray'; @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('contracts.show', $contract) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ $contract->contract_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <div>{{ $contract->project->company->company_name }}</div>
                                    <div class="text-gray-400">{{ $contract->project->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $contract->contract_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                        {{ $statusLabels[$contract->status] ?? $contract->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $contract->signed_at?->format('Y/m/d') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('contracts.edit', $contract) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>
                                    <form method="POST" action="{{ route('contracts.destroy', $contract) }}" class="inline" onsubmit="return confirm('削除してよろしいですか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">契約が登録されていません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $contracts->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
