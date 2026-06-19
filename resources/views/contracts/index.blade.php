@php
$s = fn(string $col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc']);
$i = fn(string $col) => $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : ' ↕';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">契約管理</h2>
            <a href="{{ route('contracts.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + 新規登録
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ $s('contract_number') }}" class="hover:text-gray-800">契約番号{{ $i('contract_number') }}</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">案件</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">契約種別</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ $s('status') }}" class="hover:text-gray-800">ステータス{{ $i('status') }}</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ $s('signed_at') }}" class="hover:text-gray-800">締結日{{ $i('signed_at') }}</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($contracts as $contract)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                                        {{ $contract->contract_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('companies.show', $contract->project->company) }}"
                                       class="text-gray-700 hover:text-indigo-600">
                                        {{ $contract->project->company->company_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('projects.show', $contract->project) }}"
                                       class="text-gray-700 hover:text-indigo-600">
                                        {{ $contract->project->title }}
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                    契約が登録されていません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                </div>{{-- overflow-x-auto --}}
                @if ($contracts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $contracts->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
