@php
$typeLabels = ['advertisement' => '広告掲載', 'consulting' => 'コンサルティング', 'other' => 'その他'];
@endphp

<x-editor-layout title="テンプレート作成">

    <div
        x-data="tiptapEditor({ content: {{ Js::from(old('body', '')) }} })"
        x-init="setup()"
        class="min-h-screen flex flex-col"
    >
        <form method="POST" action="{{ route('contract-templates.store') }}" class="flex flex-col flex-1">
            @csrf

            {{-- ── Sticky 2-row header ───────────────────────────────────── --}}
            <div class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">

                {{-- Row 1 --}}
                <div class="max-w-4xl mx-auto px-6 h-12 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('contract-templates.index') }}"
                           class="text-sm text-gray-400 hover:text-gray-700">← 一覧</a>
                        <span class="text-gray-200">|</span>
                        <span class="text-sm text-gray-500">新規テンプレート</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('contract-templates.index') }}"
                           class="text-sm text-gray-400 hover:text-gray-700">キャンセル</a>
                        <button type="submit"
                                class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            作成する
                        </button>
                    </div>
                </div>

                {{-- Row 2: formatting toolbar --}}
                <div class="max-w-4xl mx-auto px-6 py-2 border-t border-gray-100 flex flex-wrap items-center gap-1">
                    @include('contract-templates._editor-toolbar')
                </div>

            </div>

            {{-- ── Content area ──────────────────────────────────────────── --}}
            <div class="max-w-4xl mx-auto w-full px-6 py-10 space-y-8 flex-1">

                @if ($errors->any())
                    <div class="px-4 py-3 bg-red-50 border border-red-300 text-red-700 rounded-md text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- テンプレート名 --}}
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
                        テンプレート名 <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title') }}"
                           placeholder="例：業務委託契約書"
                           class="block w-full border-0 border-b border-gray-200 focus:border-indigo-400 focus:ring-0 text-xl font-semibold text-gray-900 placeholder-gray-300 py-2 bg-transparent @error('title') border-red-400 @enderror">
                    @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 対象種別 --}}
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">対象種別</label>
                    <select name="type"
                            class="border-gray-200 rounded-md text-sm text-gray-700 focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">全種別（どの契約種別にも表示）</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                                {{ $typeLabels[$type] ?? $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 変数チートシート --}}
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-md text-xs text-blue-800">
                    <p class="font-medium mb-2">使用できる変数（クリックで挿入）</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($variables as $var => $desc)
                            <button type="button"
                                    onclick="window._tiptapEditor?.insertText('{{ $var }}'); this.classList.add('ring-1','ring-blue-400'); setTimeout(() => this.classList.remove('ring-1','ring-blue-400'), 500)"
                                    class="px-2 py-0.5 bg-white border border-blue-300 rounded font-mono hover:bg-blue-100 transition-colors"
                                    title="{{ $desc }}">{{ $var }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- 本文エディタ --}}
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
                        本文 <span class="text-red-400">*</span>
                    </label>
                    <div class="border border-gray-200 rounded-lg overflow-hidden
                                focus-within:ring-1 focus-within:ring-indigo-400 focus-within:border-indigo-400">
                        <div data-tiptap class="bg-white"></div>
                        <textarea name="body" data-tiptap-value class="hidden">{{ old('body') }}</textarea>
                    </div>
                    @error('body') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

            </div>
        </form>
    </div>

</x-editor-layout>
