<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">請求管理</h2>
            <a href="{{ route('invoices.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + 新規作成
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">請求番号</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客 / 案件</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">税込合計</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">入金済</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">未入金</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">発行日</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">支払期限</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($invoices as $invoice)
                            @php
                                $paid    = $invoice->payments_sum_amount ?? 0;
                                $unpaid  = max(0, $invoice->total_amount - $paid);
                                $overdue = $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                       class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <div class="text-gray-700">{{ $invoice->project->company->company_name }}</div>
                                    <div class="text-gray-400 text-xs">{{ $invoice->project->title }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $invoice->title }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 font-medium">
                                    ¥{{ number_format($invoice->total_amount) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-700">
                                    ¥{{ number_format($paid) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right {{ $overdue ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                                    ¥{{ number_format($unpaid) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-status-badge :status="$invoice->status" type="invoice" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                    {{ $invoice->issued_at?->format('Y/m/d') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs {{ $overdue ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                    {{ $invoice->due_date?->format('Y/m/d') ?? '—' }}
                                    @if ($overdue) <span class="ml-1">⚠</span> @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('invoices.edit', $invoice) }}"
                                       class="text-indigo-600 hover:text-indigo-900">編集</a>
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                                          class="inline"
                                          onsubmit="return confirm('「{{ $invoice->invoice_number }}」を削除してよろしいですか？')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-400">
                                    請求書が登録されていません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($invoices->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
