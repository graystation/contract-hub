<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ダッシュボード</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- CRM summary --}}
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">基本情報</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ([
                        ['label' => '顧客数', 'value' => $companyCount, 'unit' => '社', 'route' => 'companies.index'],
                        ['label' => '案件数', 'value' => $projectCount, 'unit' => '件', 'route' => 'projects.index'],
                        ['label' => '契約数', 'value' => $contractCount, 'unit' => '件', 'route' => 'contracts.index'],
                    ] as $card)
                        <div class="bg-white shadow-sm rounded-lg p-5">
                            <div class="text-sm font-medium text-gray-500">{{ $card['label'] }}</div>
                            <div class="mt-1 flex items-baseline gap-1">
                                <span class="text-3xl font-bold text-gray-900">{{ number_format($card['value']) }}</span>
                                <span class="text-sm text-gray-400">{{ $card['unit'] }}</span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route($card['route']) }}" class="text-xs text-indigo-600 hover:text-indigo-900">
                                    一覧を見る →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- All-time billing summary --}}
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    請求・入金サマリー（全期間）
                </h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">請求総額</div>
                        <div class="mt-1 text-xl font-bold text-gray-900">{{ fmt_amount($invoiceTotalAmount) }}</div>
                        <div class="mt-3">
                            <a href="{{ route('invoices.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900">
                                一覧を見る →
                            </a>
                        </div>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">入金済総額</div>
                        <div class="mt-1 text-xl font-bold text-green-700">{{ fmt_amount($paymentTotalAmount) }}</div>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">未入金総額</div>
                        <div class="mt-1 text-xl font-bold {{ $unpaidAmount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ fmt_amount($unpaidAmount) }}
                        </div>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">未入金請求件数</div>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-3xl font-bold {{ $unpaidCount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $unpaidCount }}
                            </span>
                            <span class="text-sm text-gray-400">件</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- This month billing --}}
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    今月の請求・入金（{{ now()->format('Y年n月') }}）
                </h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">今月の請求総額</div>
                        <div class="mt-1 text-xl font-bold text-gray-900">{{ fmt_amount($monthlyInvoiceTotal) }}</div>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">今月の入金済</div>
                        <div class="mt-1 text-xl font-bold text-green-700">{{ fmt_amount($monthlyPaymentTotal) }}</div>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">今月の未入金</div>
                        <div class="mt-1 text-xl font-bold {{ $monthlyUnpaidTotal > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ fmt_amount($monthlyUnpaidTotal) }}
                        </div>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">今月の請求件数</div>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-3xl font-bold text-gray-900">{{ $monthlyInvoiceCount }}</span>
                            <span class="text-sm text-gray-400">件</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Unpaid invoice list --}}
            @if ($unpaidInvoiceList->isNotEmpty())
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    未入金請求一覧（最大10件）
                </h3>
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">請求番号</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">顧客 / 案件</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">請求額</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">入金済</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">未入金</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">支払期限</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">状態</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($unpaidInvoiceList as $inv)
                                <tr class="hover:bg-gray-50 {{ $inv->is_overdue ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('invoices.show', $inv) }}"
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="text-gray-700">{{ $inv->project->company->company_name }}</div>
                                        <div class="text-gray-400 text-xs">{{ $inv->project->title }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                        {{ fmt_amount($inv->total_amount) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-700">
                                        {{ fmt_amount($inv->paid_amount) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-red-600">
                                        {{ fmt_amount($inv->unpaid_amount) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs">
                                        @if ($inv->due_date)
                                            <span class="{{ $inv->is_overdue ? 'text-red-600 font-semibold' : ($inv->is_due_soon ? 'text-yellow-700 font-medium' : 'text-gray-600') }}">
                                                {{ $inv->due_date->format('Y/m/d') }}
                                                @if ($inv->is_overdue) <span class="ml-1">期限超過</span> @endif
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <x-invoice-status :invoice="$inv" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
