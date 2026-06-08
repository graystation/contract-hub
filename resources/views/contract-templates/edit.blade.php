<x-app-layout>

    <form method="POST" action="{{ route('contract-templates.update', $template) }}">
        @csrf @method('PUT')

        {{-- Sticky editor header bar — sticks at top when nav scrolls away --}}
        <div class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <div class="min-w-0 mr-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">テンプレート編集</p>
                    <h2 class="text-sm font-semibold text-gray-800 truncate">{{ $template->title }}</h2>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('contract-templates.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-800">キャンセル</a>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        更新する
                    </button>
                </div>
            </div>
        </div>

        {{-- Form content --}}
        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-300 text-red-700 rounded-md text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    @include('contract-templates._form')
                </div>

            </div>
        </div>

    </form>

</x-app-layout>
