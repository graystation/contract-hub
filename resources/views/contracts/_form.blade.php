@php
$statusLabels = ['draft' => '下書き', 'sent' => '送付済', 'signed' => '締結済', 'cancelled' => 'キャンセル'];
$currentProjectId      = old('project_id', $contract->project_id ?? '');
$currentContractNumber = old('contract_number', $contract->contract_number ?? $contractNumber ?? '');
$currentContractType   = old('contract_type', $contract->contract_type ?? '');
$currentBody           = old('body', $contract->body ?? '');
@endphp

<div
    x-data="{
        projectId:      '{{ $currentProjectId }}',
        contractNumber: {{ Js::from($currentContractNumber) }},
        contractType:   {{ Js::from($currentContractType) }},
        templateId:     '',
        loading:        false,

        applyTemplate() {
            if (!this.templateId || !this.projectId) return;
            this.loading = true;
            const url = `/contract-templates/${this.templateId}/preview`
                + `?project_id=${this.projectId}`
                + `&contract_number=${encodeURIComponent(this.contractNumber)}`
                + `&contract_type=${encodeURIComponent(this.contractType)}`;
            fetch(url, { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    window.dispatchEvent(new CustomEvent('load-html', {
                        detail: { target: 'body', html: data.body }
                    }));
                })
                .catch(err => console.error('Template fetch error:', err))
                .finally(() => { this.loading = false; });
        }
    }"
    class="space-y-5"
>
    {{-- Project --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">案件 <span class="text-red-500">*</span></label>
        <select name="project_id" x-model="projectId"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('project_id') border-red-500 @enderror">
            <option value="">-- 選択してください --</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ $currentProjectId == $project->id ? 'selected' : '' }}>
                    {{ $project->company->company_name }} / {{ $project->title }}
                </option>
            @endforeach
        </select>
        @error('project_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Contract number --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">契約番号 <span class="text-red-500">*</span></label>
        <input type="text" name="contract_number" x-model="contractNumber"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('contract_number') border-red-500 @enderror">
        @error('contract_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Contract type --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">契約種別 <span class="text-red-500">*</span></label>
        <input type="text" name="contract_type" x-model="contractType"
            placeholder="例：業務委託契約、広告掲載契約"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('contract_type') border-red-500 @enderror">
        @error('contract_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">ステータス <span class="text-red-500">*</span></label>
        <select name="status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('status') border-red-500 @enderror">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $contract->status ?? 'draft') === $status ? 'selected' : '' }}>
                    {{ $statusLabels[$status] }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Signed at --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">締結日</label>
        <input type="date" name="signed_at"
            value="{{ old('signed_at', isset($contract) ? $contract->signed_at?->format('Y-m-d') : '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">備考</label>
        <textarea name="notes" rows="2"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('notes', $contract->notes ?? '') }}</textarea>
    </div>

    {{-- Template selector + body editor --}}
    <div class="pt-2 border-t border-gray-100">
        <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-semibold text-gray-700">契約書本文</label>
            @if (isset($templates) && $templates->isNotEmpty())
                <div class="flex items-center gap-2">
                    <select x-model="templateId"
                            class="text-sm border-gray-300 rounded-md shadow-sm py-1">
                        <option value="">テンプレートを選ぶ…</option>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->title }}</option>
                        @endforeach
                    </select>
                    <button type="button"
                            @click="applyTemplate()"
                            :disabled="!templateId || loading"
                            class="px-3 py-1 text-xs font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed">
                        <span x-show="!loading">適用</span>
                        <span x-show="loading">読込中…</span>
                    </button>
                    <a href="{{ route('contract-templates.index') }}" target="_blank"
                       class="text-xs text-gray-400 hover:text-indigo-600">テンプレート管理</a>
                </div>
            @else
                <a href="{{ route('contract-templates.create') }}" target="_blank"
                   class="text-xs text-indigo-600 hover:text-indigo-800">
                    + テンプレートを作成
                </a>
            @endif
        </div>

        <p class="text-xs text-gray-400 mb-2">
            テンプレートに
            <code class="bg-gray-100 px-1 rounded">{company_name}</code>
            <code class="bg-gray-100 px-1 rounded">{contact_name}</code>
            <code class="bg-gray-100 px-1 rounded">{project_title}</code>
            <code class="bg-gray-100 px-1 rounded">{contract_number}</code>
            <code class="bg-gray-100 px-1 rounded">{today}</code>
            などを使うと自動で値が入ります。
        </p>

        <x-tiptap-editor name="body" :value="$currentBody" />
        @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

</div>
