<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">契約登録</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('contracts.store') }}">
                    @csrf
                    @include('contracts._form')
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                            登録する
                        </button>
                        <a href="{{ route('contracts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
