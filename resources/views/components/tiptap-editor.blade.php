@props(['name' => 'body', 'value' => ''])

<div
    x-data="tiptapEditor({ content: {{ Js::from($value) }} })"
    x-init="init()"
    @load-html.window="if ($event.detail.target === '{{ $name }}') loadHtml($event.detail.html)"
    class="border border-gray-300 rounded-md overflow-hidden focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500"
>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 px-3 py-2 bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
        <button type="button" @click="toggleH2()"
            :class="isActive('heading', {level:2}) ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs font-bold rounded transition-colors">H2</button>

        <button type="button" @click="toggleH3()"
            :class="isActive('heading', {level:3}) ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs font-bold rounded transition-colors">H3</button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        <button type="button" @click="toggleBold()"
            :class="isActive('bold') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs font-bold rounded transition-colors">B</button>

        <button type="button" @click="toggleUnderline()"
            :class="isActive('underline') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs underline rounded transition-colors">U</button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        <button type="button" @click="toggleBullet()"
            :class="isActive('bulletList') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs rounded transition-colors">• リスト</button>

        <button type="button" @click="toggleOrdered()"
            :class="isActive('orderedList') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs rounded transition-colors">1. リスト</button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        <button type="button" @click="clearFormat()"
            class="px-2 py-1 text-xs rounded text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition-colors">
            書式解除
        </button>
    </div>

    {{-- Tiptap content area --}}
    <div data-tiptap class="bg-white"></div>

    {{-- Hidden input synced to Tiptap HTML output --}}
    <textarea name="{{ $name }}" data-tiptap-value class="hidden">{{ $value }}</textarea>
</div>
