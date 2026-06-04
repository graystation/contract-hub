@php
$typeLabels = ['advertisement' => '広告掲載', 'consulting' => 'コンサルティング', 'other' => 'その他'];
$statusLabels = ['draft' => '下書き', 'active' => '進行中', 'completed' => '完了', 'cancelled' => 'キャンセル'];
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">顧客 <span class="text-red-500">*</span></label>
        <select name="company_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('company_id') border-red-500 @enderror">
            <option value="">-- 選択してください --</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" {{ old('company_id', $project->company_id ?? '') == $company->id ? 'selected' : '' }}>
                    {{ $company->company_name }}
                </option>
            @endforeach
        </select>
        @error('company_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">案件名 <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $project->title ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('title') border-red-500 @enderror">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">種別 <span class="text-red-500">*</span></label>
        <select name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('type') border-red-500 @enderror">
            @foreach ($types as $type)
                <option value="{{ $type }}" {{ old('type', $project->type ?? '') === $type ? 'selected' : '' }}>
                    {{ $typeLabels[$type] }}
                </option>
            @endforeach
        </select>
        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">ステータス <span class="text-red-500">*</span></label>
        <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('status') border-red-500 @enderror">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $project->status ?? 'draft') === $status ? 'selected' : '' }}>
                    {{ $statusLabels[$status] }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">説明</label>
        <textarea name="description" rows="4"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('description') border-red-500 @enderror">{{ old('description', $project->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">開始日</label>
            <input type="date" name="started_at" value="{{ old('started_at', isset($project) ? $project->started_at?->format('Y-m-d') : '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('started_at') border-red-500 @enderror">
            @error('started_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">終了日</label>
            <input type="date" name="ended_at" value="{{ old('ended_at', isset($project) ? $project->ended_at?->format('Y-m-d') : '') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('ended_at') border-red-500 @enderror">
            @error('ended_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
