@php
$typeLabels = ['advertisement' => '広告掲載', 'consulting' => 'コンサルティング', 'other' => 'その他'];
@endphp

<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-gray-700">テンプレート名 <span class="text-red-500">*</span></label>
        <input type="text" name="title"
               value="{{ old('title', $template->title ?? '') }}"
               placeholder="例：業務委託契約書"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('title') border-red-500 @enderror">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">対象種別</label>
        <select name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
            <option value="">全種別（どの契約種別にも表示）</option>
            @foreach ($types as $type)
                <option value="{{ $type }}" {{ old('type', $template->type ?? '') === $type ? 'selected' : '' }}>
                    {{ $typeLabels[$type] ?? $type }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700">本文 <span class="text-red-500">*</span></label>
        </div>

        {{-- Variable cheat sheet --}}
        <div class="mb-2 p-3 bg-blue-50 border border-blue-200 rounded-md text-xs text-blue-800">
            <p class="font-medium mb-1">使用できる変数（クリックでコピー）</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($variables as $var => $desc)
                    <button type="button"
                            onclick="navigator.clipboard.writeText('{{ $var }}').then(() => { this.classList.add('bg-blue-200'); setTimeout(() => this.classList.remove('bg-blue-200'), 800) })"
                            class="px-2 py-0.5 bg-white border border-blue-300 rounded font-mono hover:bg-blue-100 transition-colors"
                            title="{{ $desc }}">
                        {{ $var }}
                    </button>
                @endforeach
            </div>
        </div>

        <x-tiptap-editor name="body" :value="old('body', $template->body ?? '')" stickyTopClass="top-[60px]" />
        @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
