@php
$methodLabels = \App\Models\Payment::METHOD_LABELS;
$isEditing = isset($editingPayment);
$payment = $editingPayment ?? null;
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">入金額（円） <span class="text-red-500">*</span></label>
            <input type="number" name="amount" min="1"
                   value="{{ old('amount', $payment->amount ?? '') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('amount') border-red-500 @enderror">
            @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">入金日 <span class="text-red-500">*</span></label>
            <input type="date" name="paid_at"
                   value="{{ old('paid_at', isset($payment) ? $payment->paid_at?->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('paid_at') border-red-500 @enderror">
            @error('paid_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">入金方法 <span class="text-red-500">*</span></label>
        <select name="method"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm @error('method') border-red-500 @enderror">
            @foreach ($methodLabels as $value => $label)
                <option value="{{ $value }}" {{ old('method', $payment->method ?? 'bank_transfer') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">メモ</label>
        <input type="text" name="memo" value="{{ old('memo', $payment->memo ?? '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
    </div>
</div>
