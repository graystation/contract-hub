@php
$statusLabels = ['draft' => '下書き', 'sent' => '送付済', 'signed' => '締結済', 'cancelled' => 'キャンセル'];
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">案件 <span class="text-red-500">*</span></label>
        <select name="project_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('project_id') border-red-500 @enderror">
            <option value="">-- 選択してください --</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ old('project_id', $contract->project_id ?? '') == $project->id ? 'selected' : '' }}>
                    {{ $project->company->company_name }} / {{ $project->title }}
                </option>
            @endforeach
        </select>
        @error('project_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">契約番号 <span class="text-red-500">*</span></label>
        <input type="text" name="contract_number" value="{{ old('contract_number', $contract->contract_number ?? $contractNumber ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('contract_number') border-red-500 @enderror">
        @error('contract_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">契約種別 <span class="text-red-500">*</span></label>
        <input type="text" name="contract_type" value="{{ old('contract_type', $contract->contract_type ?? '') }}"
            placeholder="例：業務委託契約、広告掲載契約"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('contract_type') border-red-500 @enderror">
        @error('contract_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">ステータス <span class="text-red-500">*</span></label>
        <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('status') border-red-500 @enderror">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $contract->status ?? 'draft') === $status ? 'selected' : '' }}>
                    {{ $statusLabels[$status] }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">締結日</label>
        <input type="date" name="signed_at" value="{{ old('signed_at', isset($contract) ? $contract->signed_at?->format('Y-m-d') : '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('signed_at') border-red-500 @enderror">
        @error('signed_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">備考</label>
        <textarea name="notes" rows="3"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('notes') border-red-500 @enderror">{{ old('notes', $contract->notes ?? '') }}</textarea>
        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
