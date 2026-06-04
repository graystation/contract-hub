@php
$totalPaid   = $invoice->payments->sum('amount');
$remaining   = $invoice->total_amount - $totalPaid;
$isOverpaid  = $totalPaid > $invoice->total_amount;

$auditActionLabels = [
    'invoice_created'                  => '請求書作成',
    'invoice_updated'                  => '請求書更新',
    'invoice_deleted'                  => '請求書削除',
    'payment_created'                  => '入金登録',
    'payment_updated'                  => '入金更新',
    'payment_deleted'                  => '入金削除',
    'invoice_status_updated_to_paid'   => 'ステータス → 入金済',
    'invoice_status_updated_to_issued' => 'ステータス → 請求済',
];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-xs text-gray-500 mb-1">
                    <a href="{{ route('companies.show', $invoice->project->company) }}"
                       class="hover:text-indigo-600">{{ $invoice->project->company->company_name }}</a>
                    <span class="mx-1">›</span>
                    <a href="{{ route('projects.show', $invoice->project) }}"
                       class="hover:text-indigo-600">{{ $invoice->project->title }}</a>
                    <span class="mx-1">›</span>請求
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $invoice->invoice_number }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('invoices.edit', $invoice) }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    編集
                </a>
                <a href="{{ route('invoices.index') }}"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    一覧へ戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash messages --}}
            @foreach (['success' => 'green', 'warning' => 'yellow', 'error' => 'red'] as $type => $color)
                @if (session($type))
                    <div class="px-4 py-3 bg-{{ $color }}-50 border border-{{ $color }}-300 text-{{ $color }}-800 rounded-md text-sm">
                        {{ session($type) }}
                    </div>
                @endif
            @endforeach

            {{-- Section 1: Invoice info --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">1. 請求情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">請求番号</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $invoice->invoice_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">タイトル</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $invoice->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">ステータス</dt>
                            <dd class="mt-1"><x-status-badge :status="$invoice->status" type="invoice" /></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">発行日 / 支払期限</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $invoice->issued_at?->format('Y/m/d') ?? '—' }}
                                @if ($invoice->due_date)
                                    <span class="text-gray-400 mx-1">/</span>
                                    <span class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-red-600 font-medium' : '' }}">
                                        {{ $invoice->due_date->format('Y/m/d') }}
                                    </span>
                                @endif
                            </dd>
                        </div>
                        @if ($invoice->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">備考</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $invoice->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Section 2: Customer / project --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">2. 顧客・案件情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('companies.show', $invoice->project->company) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $invoice->project->company->company_name }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">案件</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('projects.show', $invoice->project) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $invoice->project->title }}
                                </a>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Section 3: Amount breakdown --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border-l-4 {{ $invoice->status === 'paid' ? 'border-green-500' : ($isOverpaid ? 'border-yellow-400' : 'border-gray-300') }}">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">3. 金額内訳</h3>
                </div>
                <div class="p-6">
                    @if ($isOverpaid)
                        <div class="mb-4 px-3 py-2 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded text-sm">
                            ⚠ 過入金が発生しています（過払い額：¥{{ number_format($totalPaid - $invoice->total_amount) }}）
                        </div>
                    @endif
                    <dl class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">税抜金額</dt>
                            <dd class="text-gray-900">¥{{ number_format($invoice->amount) }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">消費税（10%）</dt>
                            <dd class="text-gray-900">¥{{ number_format($invoice->tax_amount) }}</dd>
                        </div>
                        <div class="flex justify-between font-medium border-t border-gray-100 pt-2">
                            <dt class="text-gray-700">税込合計</dt>
                            <dd class="text-gray-900 text-base">¥{{ number_format($invoice->total_amount) }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-green-700">入金済額</dt>
                            <dd class="text-green-700 font-medium">¥{{ number_format($totalPaid) }}</dd>
                        </div>
                        <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                            <dt class="{{ $remaining > 0 ? 'text-red-600' : 'text-gray-500' }}">未入金額</dt>
                            <dd class="{{ $remaining > 0 ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                ¥{{ number_format(max(0, $remaining)) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Section 4: Payments --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        4. 入金一覧
                        <span class="ml-2 text-gray-400 font-normal normal-case">{{ $invoice->payments->count() }} 件</span>
                    </h3>
                </div>

                @if ($invoice->payments->isEmpty())
                    <div class="px-6 py-6 text-center text-sm text-gray-400">入金が登録されていません。</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">入金日</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">金額</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">方法</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">メモ</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($invoice->payments as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {{ $payment->paid_at->format('Y/m/d') }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                        ¥{{ number_format($payment->amount) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {{ \App\Models\Payment::METHOD_LABELS[$payment->method] ?? $payment->method }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $payment->memo ?? '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('payments.edit', $payment) }}"
                                           class="text-indigo-600 hover:text-indigo-900">編集</a>
                                        <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                                              class="inline"
                                              onsubmit="return confirm('この入金記録を削除してよろしいですか？')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- Payment registration form --}}
                @if ($invoice->status !== 'cancelled')
                    @php $ep = $editingPayment ?? null; @endphp
                    <div class="border-t border-gray-100">
                        <div class="px-6 py-4 bg-gray-50">
                            <h4 class="text-sm font-medium text-gray-700">
                                {{ $ep ? '入金情報を編集' : '+ 入金を登録' }}
                            </h4>
                        </div>
                        <div class="p-6">
                            @if ($ep)
                                <form method="POST" action="{{ route('payments.update', $ep) }}">
                                    @csrf @method('PUT')
                                    @include('payments._form', ['editingPayment' => $ep])
                                    <div class="mt-4 flex gap-3">
                                        <button type="submit"
                                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                            更新する
                                        </button>
                                        <a href="{{ route('invoices.show', $invoice) }}"
                                           class="text-sm text-gray-600 hover:text-gray-900 self-center">キャンセル</a>
                                    </div>
                                </form>
                            @else
                                <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}">
                                    @csrf
                                    @include('payments._form')
                                    <div class="mt-4">
                                        <button type="submit"
                                                class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700">
                                            入金を登録する
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Section 5: Audit logs --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        5. 監査ログ
                        <span class="ml-2 text-gray-400 font-normal normal-case">最新{{ $auditLogs->count() }}件</span>
                    </h3>
                </div>
                @if ($auditLogs->isEmpty())
                    <div class="px-6 py-6 text-center text-sm text-gray-400">ログがありません。</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">日時</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">アクション</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($auditLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-700">
                                        {{ $log->created_at->format('Y/m/d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-700">
                                        {{ $auditActionLabels[$log->action] ?? $log->action }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500 font-mono">
                                        {{ $log->ip_address ?? '—' }}
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
