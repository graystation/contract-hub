<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ダッシュボード</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Billing summary --}}
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">請求・入金サマリー</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">請求総額</div>
                        <div class="mt-1 text-xl font-bold text-gray-900">
                            ¥{{ number_format($invoiceTotalAmount) }}
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('invoices.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900">
                                一覧を見る →
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">入金済総額</div>
                        <div class="mt-1 text-xl font-bold text-green-700">
                            ¥{{ number_format($paymentTotalAmount) }}
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="text-sm font-medium text-gray-500">未入金総額</div>
                        <div class="mt-1 text-xl font-bold {{ $unpaidAmount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            ¥{{ number_format($unpaidAmount) }}
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
                        <div class="mt-3">
                            <a href="{{ route('invoices.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900">
                                確認する →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
