@php
$statusLabels = ['draft' => '下書き', 'issued' => '請求済', 'paid' => '入金済', 'cancelled' => 'キャンセル'];
@endphp

<div x-data="{ amount: {{ old('amount', $invoice->amount ?? 0) }} }" class="space-y-5">

    <div>
        <label class="block text-sm font-medium text-gray-700">案件 <span class="text-red-500">*</span></label>
        <select name="project_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('project_id') border-red-500 @enderror">
            <option value="">-- 選択してください --</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ old('project_id', $invoice->project_id ?? '') == $project->id ? 'selected' : '' }}>
                    {{ $project->company->company_name }} / {{ $project->title }}
                </option>
            @endforeach
        </select>
        @error('project_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">タイトル <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $invoice->title ?? '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('title') border-red-500 @enderror">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">税抜金額（円） <span class="text-red-500">*</span></label>
        <input type="number" name="amount" x-model="amount" min="0"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('amount') border-red-500 @enderror">
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
                   value="{{ old('issued_at', isset($invoice) ? $invoice->issued_at?->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
            @error('issued_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">支払期限</label>
            <input type="date" name="due_date"
                   value="{{ old('due_date', isset($invoice) ? $invoice->due_date?->format('Y-m-d') : '') }}"
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

</div>
