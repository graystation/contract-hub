@php
$statusLabels = ['draft' => '下書き', 'issued' => '請求済', 'paid' => '入金済', 'cancelled' => 'キャンセル'];
$selectedProjectId = old('project_id', $invoice->project_id ?? $fromContract?->project_id ?? '');
$selectedContractId = old('contract_id', $invoice->contract_id ?? $fromContract?->id ?? '');
@endphp

<div x-data="{
    amount: {{ old('amount', $invoice->amount ?? 0) }},
    displayAmount: '',
    selectedProjectId: '{{ $selectedProjectId }}',
    init() {
        this.displayAmount = this.amount > 0 ? this.amount.toLocaleString() : '';
    },
    onAmountInput(e) {
        const raw = e.target.value.replace(/[^\d]/g, '');
        this.amount = raw === '' ? 0 : parseInt(raw, 10);
        this.displayAmount = raw === '' ? '' : this.amount.toLocaleString();
        e.target.value = this.displayAmount;
    }
}" class="space-y-5">

    <div>
        <label class="block text-sm font-medium text-gray-700">案件 <span class="text-red-500">*</span></label>
        <select name="project_id" x-model="selectedProjectId"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('project_id') border-red-500 @enderror">
            <option value="">-- 選択してください --</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                    {{ $project->company->company_name }} / {{ $project->title }}
                </option>
            @endforeach
        </select>
        @error('project_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">紐づく契約（任意）</label>
        <select name="contract_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('contract_id') border-red-500 @enderror">
            <option value="">-- 選択しない --</option>
            @foreach ($contracts as $contract)
                <template x-if="selectedProjectId == '{{ $contract->project_id }}'">
                    <option value="{{ $contract->id }}" {{ $selectedContractId == $contract->id ? 'selected' : '' }}>
                        {{ $contract->contract_number }} — {{ $contract->project->company->company_name }}
                    </option>
                </template>
            @endforeach
        </select>
        @error('contract_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">件名 <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $invoice->title ?? $defaultTitle ?? '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('title') border-red-500 @enderror">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">税抜金額（円） <span class="text-red-500">*</span></label>
        <input type="text" inputmode="numeric" x-model="displayAmount" @input="onAmountInput($event)"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('amount') border-red-500 @enderror">
        <input type="hidden" name="amount" :value="amount">
        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        <div class="mt-2 p-3 bg-gray-50 rounded-md text-sm text-gray-600 space-y-1">
            <div class="flex justify-between">
                <span>消費税（10%）</span>
                <span x-text="'¥' + Math.round(amount * 0.1).toLocaleString()">¥0</span>
            </div>
            <div class="flex justify-between font-medium text-gray-800 border-t border-gray-200 pt-1">
                <span>税込合計</span>
                <span x-text="'¥' + (parseInt(amount || 0) + Math.round(amount * 0.1)).toLocaleString()">¥0</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">発行日</label>
            <input type="date" name="issued_at"
                   value="{{ old('issued_at', isset($invoice) ? $invoice->issued_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
            @error('issued_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">支払期限</label>
            <input type="date" name="due_date"
                   value="{{ old('due_date', isset($invoice) ? $invoice->due_date?->format('Y-m-d') : now()->addDays(7)->format('Y-m-d')) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
            @error('due_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">ステータス <span class="text-red-500">*</span></label>
        <select name="status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('status') border-red-500 @enderror">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $invoice->status ?? 'draft') === $status ? 'selected' : '' }}>
                    {{ $statusLabels[$status] }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">備考</label>
        <textarea name="notes" rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('notes', $invoice->notes ?? '') }}</textarea>
        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">領収書但し書き</label>
        <input type="text" name="receipt_description"
               value="{{ old('receipt_description', $invoice->receipt_description ?? '') }}"
               placeholder="例：HP広告掲載費　（空欄の場合はタイトルを使用）"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('receipt_description') border-red-500 @enderror">
        @error('receipt_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

</div>
