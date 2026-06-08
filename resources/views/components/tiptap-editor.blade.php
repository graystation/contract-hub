@props(['name' => 'body', 'value' => '', 'stickyTopClass' => 'top-0'])

<div
    x-data="tiptapEditor({ content: {{ Js::from($value) }} })"
    x-init="init()"
    @load-html.window="if ($event.detail.target === '{{ $name }}') loadHtml($event.detail.html)"
    class="border border-gray-300 rounded-md overflow-hidden focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500"
>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 px-3 py-2 bg-gray-50 border-b border-gray-200 sticky {{ $stickyTopClass }} z-20">

        {{-- Headings --}}
        <button type="button" @click="toggleH2()"
            :class="isActive('heading',{level:2}) ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs font-bold rounded transition-colors">H2</button>
        <button type="button" @click="toggleH3()"
            :class="isActive('heading',{level:3}) ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs font-bold rounded transition-colors">H3</button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        {{-- Inline format --}}
        <button type="button" @click="toggleBold()"
            :class="isActive('bold') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs font-bold rounded transition-colors">B</button>
        <button type="button" @click="toggleItalic()"
            :class="isActive('italic') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs italic rounded transition-colors">I</button>
        <button type="button" @click="toggleUnderline()"
            :class="isActive('underline') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs underline rounded transition-colors">U</button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        {{-- Alignment --}}
        <button type="button" @click="alignLeft()"
            :class="isActive({textAlign:'left'}) ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs rounded transition-colors" title="左揃え">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 4h16v1.5H2V4zm0 4h10v1.5H2V8zm0 4h16v1.5H2V12zm0 4h10v1.5H2V16z" clip-rule="evenodd"/></svg>
        </button>
        <button type="button" @click="alignCenter()"
            :class="isActive({textAlign:'center'}) ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs rounded transition-colors" title="中央揃え">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 4h16v1.5H2V4zm3 4h10v1.5H5V8zm-3 4h16v1.5H2V12zm3 4h10v1.5H5V16z" clip-rule="evenodd"/></svg>
        </button>
        <button type="button" @click="alignRight()"
            :class="isActive({textAlign:'right'}) ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs rounded transition-colors" title="右揃え">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 4h16v1.5H2V4zm6 4h10v1.5H8V8zm-6 4h16v1.5H2V12zm6 4h10v1.5H8V16z" clip-rule="evenodd"/></svg>
        </button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        {{-- Lists --}}
        <button type="button" @click="toggleBullet()"
            :class="isActive('bulletList') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs rounded transition-colors">• リスト</button>
        <button type="button" @click="toggleOrdered()"
            :class="isActive('orderedList') ? 'bg-gray-300 text-gray-900' : 'text-gray-600 hover:bg-gray-200'"
            class="px-2 py-1 text-xs rounded transition-colors">1. リスト</button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        {{-- Indent --}}
        <button type="button" @click="outdent()"
            class="px-2 py-1 text-xs rounded text-gray-600 hover:bg-gray-200 transition-colors" title="インデント減（Shift+Tab）">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12M3 9h18"/></svg>
        </button>
        <button type="button" @click="indent()"
            class="px-2 py-1 text-xs rounded text-gray-600 hover:bg-gray-200 transition-colors" title="インデント増（Tab）">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H3m18 0H9"/></svg>
        </button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        {{-- Horizontal rule --}}
        <button type="button" @click="insertHr()"
            class="px-2 py-1 text-xs rounded text-gray-600 hover:bg-gray-200 transition-colors" title="区切り線">
            ——
        </button>

        <span class="w-px h-4 bg-gray-300 mx-1"></span>

        {{-- Clear --}}
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
