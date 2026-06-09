@php
$statusLabels  = ['draft' => '下書き', 'sent' => '送付済', 'signed' => '締結済', 'cancelled' => 'キャンセル'];
$currentProjectId      = old('project_id',      $contract->project_id      ?? '');
$currentContractNumber = old('contract_number',  $contract->contract_number ?? '');
$currentContractType   = old('contract_type',    $contract->contract_type   ?? '');
$currentBody           = old('body',             $contract->body            ?? '');

$projectMap  = [];
foreach (($projects ?? []) as $p) {
    $projectMap[$p->id] = [
        'company_name'  => $p->company->company_name ?? '',
        'contact_name'  => $p->company->contact_name ?? '',
        'project_title' => $p->title ?? '',
        'start_date'    => $p->started_at?->format('Y年m月d日') ?? '',
        'end_date'      => $p->ended_at?->format('Y年m月d日') ?? '',
    ];
}
$templateMap = [];
foreach (($templates ?? []) as $t) {
    $templateMap[$t->id] = $t->body;
}
@endphp

<x-editor-layout :title="'契約編集 — ' . $contract->contract_number">

<script>
window.__contractTemplates__ = @json($templateMap);
window.__contractProjects__  = @json($projectMap);
</script>

<div
    x-data="tiptapEditor({ content: {{ Js::from($currentBody) }} })"
    x-init="setup()"
    @load-html.window="if ($event.detail.target === 'body') loadHtml($event.detail.html)"
    class="min-h-screen flex flex-col"
>
    <form method="POST" action="{{ route('contracts.update', $contract) }}" class="flex flex-col flex-1">
        @csrf
        @method('PUT')

        {{-- ── Sticky 2-row header ─────────────────────────────────────── --}}
        <div class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">

            {{-- Row 1: breadcrumb / actions --}}
            <div class="max-w-5xl mx-auto px-6 h-12 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('contracts.show', $contract) }}"
                       class="text-sm text-gray-400 hover:text-gray-700 shrink-0">← 詳細</a>
                    <span class="text-gray-200 shrink-0">|</span>
                    <span class="text-sm text-gray-700 font-medium truncate">{{ $contract->contract_number }}</span>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <a href="{{ route('contracts.show', $contract) }}"
                       class="text-sm text-gray-400 hover:text-gray-700">キャンセル</a>
                    <button type="submit"
                            class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        更新する
                    </button>
                </div>
            </div>

            {{-- Row 2: formatting toolbar --}}
            <div class="max-w-5xl mx-auto px-6 py-2 border-t border-gray-100 flex flex-wrap items-center gap-1">
                @include('components.tiptap-toolbar')
            </div>

        </div>

        {{-- ── Content area ────────────────────────────────────────────── --}}
        <div class="max-w-5xl mx-auto w-full px-6 py-8 space-y-8 flex-1">

            @if ($errors->any())
                <div class="px-4 py-3 bg-red-50 border border-red-300 text-red-700 rounded-md text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Metadata fields ──────────────────────────────────────── --}}
            <div x-data="contractForm({
                    projectId:      {{ Js::from($currentProjectId) }},
                    contractNumber: {{ Js::from($currentContractNumber) }},
                    contractType:   {{ Js::from($currentContractType) }},
                    templateId:     {{ Js::from(old('template_id', $contract->template_id ?? '')) }}
                 })"
                 class="grid grid-cols-2 gap-x-8 gap-y-5">

                {{-- 案件 --}}
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">
                        案件 <span class="text-red-400">*</span>
                    </label>
                    <select name="project_id" x-model="projectId"
                            class="block w-full border-0 border-b border-gray-200 focus:border-indigo-400 focus:ring-0 text-sm text-gray-900 bg-transparent py-1.5 @error('project_id') border-red-400 @enderror">
                        <option value="">-- 選択してください --</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" {{ $currentProjectId == $project->id ? 'selected' : '' }}>
                                {{ $project->company->company_name }} / {{ $project->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 契約番号 --}}
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">
                        契約番号 <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="contract_number" x-model="contractNumber"
                           class="block w-full border-0 border-b border-gray-200 focus:border-indigo-400 focus:ring-0 text-sm text-gray-900 bg-transparent py-1.5 @error('contract_number') border-red-400 @enderror">
                    @error('contract_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 契約種別 --}}
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">
                        契約種別 <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="contract_type" x-model="contractType"
                           placeholder="例：業務委託契約、広告掲載契約"
                           class="block w-full border-0 border-b border-gray-200 focus:border-indigo-400 focus:ring-0 text-sm text-gray-900 bg-transparent py-1.5 @error('contract_type') border-red-400 @enderror">
                    @error('contract_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- ステータス --}}
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">
                        ステータス <span class="text-red-400">*</span>
                    </label>
                    <select name="status"
                            class="block w-full border-0 border-b border-gray-200 focus:border-indigo-400 focus:ring-0 text-sm text-gray-900 bg-transparent py-1.5 @error('status') border-red-400 @enderror">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ old('status', $contract->status) === $status ? 'selected' : '' }}>
                                {{ $statusLabels[$status] }}
                            </option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 締結日 --}}
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">締結日</label>
                    <input type="date" name="signed_at"
                           value="{{ old('signed_at', $contract->signed_at?->format('Y-m-d')) }}"
                           class="block w-full border-0 border-b border-gray-200 focus:border-indigo-400 focus:ring-0 text-sm text-gray-900 bg-transparent py-1.5">
                </div>

                {{-- 備考 --}}
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">備考</label>
                    <textarea name="notes" rows="2"
                              class="block w-full border-0 border-b border-gray-200 focus:border-indigo-400 focus:ring-0 text-sm text-gray-900 bg-transparent py-1.5 resize-none">{{ old('notes', $contract->notes) }}</textarea>
                </div>

                {{-- テンプレート適用 --}}
                @if (isset($templates) && $templates->isNotEmpty())
                <div class="col-span-2 flex items-center gap-3 pt-2">
                    <span class="text-xs text-gray-400">テンプレート適用：</span>
                    <select x-model="templateId"
                            class="text-sm border-gray-200 rounded-md py-1 focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">テンプレートを選ぶ…</option>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->title }}</option>
                        @endforeach
                    </select>
                    <button type="button"
                            @click="applyTemplate()"
                            :disabled="!templateId"
                            class="px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed">
                        適用
                    </button>
                    <a href="{{ route('contract-templates.index') }}" target="_blank"
                       class="text-xs text-gray-400 hover:text-indigo-600">テンプレート管理</a>
                    <input type="hidden" name="template_id" :value="templateId || ''">
                </div>
                @endif

            </div>{{-- /contractForm --}}

            {{-- ── 本文エディター ───────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
                    本文 <span class="text-red-400">*</span>
                </label>
                <div class="border border-gray-200 rounded-lg overflow-hidden
                            focus-within:ring-1 focus-within:ring-indigo-400 focus-within:border-indigo-400">
                    <div data-tiptap class="bg-white"></div>
                    <textarea name="body" data-tiptap-value class="hidden">{{ $currentBody }}</textarea>
                </div>
                @error('body') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

        </div>
    </form>
</div>

</x-editor-layout>
