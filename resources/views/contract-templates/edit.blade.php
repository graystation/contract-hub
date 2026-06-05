<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">テンプレート編集 — {{ $template->title }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contract-templates.update', $template) }}">
                    @csrf @method('PUT')
                    @include('contract-templates._form')
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            更新する
                        </button>
                        <a href="{{ route('contract-templates.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
