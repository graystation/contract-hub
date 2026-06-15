<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">契約書テンプレート</h2>
            <a href="{{ route('contract-templates.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + 新規作成
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @if ($templates->isEmpty())
                    <div class="px-6 py-12 text-center text-sm text-gray-400">
                        <p class="mb-3">テンプレートがありません。</p>
                        <a href="{{ route('contract-templates.create') }}"
                           class="text-indigo-600 hover:text-indigo-800">+ 最初のテンプレートを作成する</a>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">テンプレート名</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">更新日</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($templates as $tpl)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $tpl->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $tpl->updated_at->format('Y/m/d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <a href="{{ route('contract-templates.edit', $tpl) }}"
                                           class="text-indigo-600 hover:text-indigo-900">編集</a>
                                        <form method="POST" action="{{ route('contract-templates.destroy', $tpl) }}"
                                              class="inline"
                                              onsubmit="return confirm('「{{ $tpl->title }}」を削除してよろしいですか？')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
