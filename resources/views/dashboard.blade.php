<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ダッシュボード
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">顧客数</div>
                        <div class="mt-1 text-3xl font-bold text-gray-900">{{ $companyCount }}</div>
                        <div class="mt-4">
                            <a href="{{ route('companies.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                一覧を見る →
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">案件数</div>
                        <div class="mt-1 text-3xl font-bold text-gray-900">{{ $projectCount }}</div>
                        <div class="mt-4">
                            <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                一覧を見る →
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">契約数</div>
                        <div class="mt-1 text-3xl font-bold text-gray-900">{{ $contractCount }}</div>
                        <div class="mt-4">
                            <a href="{{ route('contracts.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                一覧を見る →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
