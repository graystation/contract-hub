@php
$actionLabels = [
    'contract_pdf_generated'                         => 'PDF生成',
    'contract_file_hash_verified_matched'            => 'ハッシュ検証（一致）',
    'contract_file_hash_verified_mismatched'         => 'ハッシュ検証（不一致）',
    'contract_file_hash_verified_missing'            => 'ハッシュ検証（ファイルなし）',
    'contract_sign_url_generated'                    => '同意URL発行',
    'contract_signed_by_external_user'               => '電子同意完了',
    'contract_sign_request_mail_sent'                => '同意依頼メール送信',
    'contract_sign_request_mail_failed'              => '同意依頼メール送信失敗',
    'contract_signed_notification_mail_sent'         => '締結完了通知メール送信',
    'contract_signed_notification_mail_failed'       => '締結完了通知メール送信失敗',
    'contract_evidence_export_generated'             => '証跡ZIP生成',
];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-xs text-gray-500 mb-1">
                    <a href="{{ route('companies.show', $contract->project->company) }}"
                       class="hover:text-indigo-600">{{ $contract->project->company->company_name }}</a>
                    <span class="mx-1">›</span>
                    <a href="{{ route('projects.show', $contract->project) }}"
                       class="hover:text-indigo-600">{{ $contract->project->title }}</a>
                    <span class="mx-1">›</span>契約
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $contract->contract_number }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('contracts.pdf.preview', $contract) }}" target="_blank"
                   class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700">
                    PDFプレビュー
                </a>
                @unless (in_array($contract->status, ['signed', 'cancelled'], true))
                    <a href="{{ route('contracts.edit', $contract) }}"
                       class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        契約書を編集
                    </a>
                @endunless
                <a href="{{ route('contracts.index') }}"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    一覧へ戻る
                </a>
                <form method="POST" action="{{ route('contracts.duplicate', $contract) }}"
                      onsubmit="return confirm('この契約をコピーして新しいドラフトを作成します。よろしいですか？')">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                        コピーして再作成
                    </button>
                </form>
                <form method="POST" action="{{ route('contracts.destroy', $contract) }}"
                      class="ml-4 pl-4 border-l border-gray-300"
                      onsubmit="return confirmContractDelete(event, '{{ $contract->contract_number }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-white border border-red-300 text-red-600 text-sm font-medium rounded-md hover:bg-red-50">
                        削除
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="px-4 py-3 bg-green-50 border border-green-300 text-green-800 rounded-md text-sm flex items-center gap-2">
                    <span class="font-medium">✓</span> {{ session('success') }}
                </div>
            @endif
            @if (session('warning'))
                <div class="px-4 py-3 bg-yellow-50 border border-yellow-400 text-yellow-800 rounded-md text-sm flex items-center gap-2">
                    <span class="font-medium">⚠</span> {{ session('warning') }}
                </div>
            @endif
            @if (session('error'))
                <div class="px-4 py-3 bg-red-50 border border-red-400 text-red-800 rounded-md text-sm flex items-center gap-2">
                    <span class="font-medium">✕</span> {{ session('error') }}
                </div>
            @endif

            {{-- Section 1: Contract basic info --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">1. 契約情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">契約番号</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $contract->contract_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">契約種別</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $contract->contract_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">ステータス</dt>
                            <dd class="mt-1">
                                <x-status-badge :status="$contract->status" type="contract" />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">締結日時</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $contract->signed_at?->format('Y年m月d日 H:i') ?? '—' }}
                            </dd>
                        </div>
                        @if ($contract->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">備考</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $contract->notes }}</dd>
                            </div>
                        @endif
                        @if ($contract->template)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">使用テンプレート</dt>
                                <dd class="mt-1 flex items-center gap-3">
                                    <span class="text-sm text-gray-900">{{ $contract->template->title }}</span>
                                    <a href="{{ route('contract-templates.edit', $contract->template) }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800">テンプレートを編集 →</a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Contract body --}}
            @if ($contract->body)
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden" x-data="{ open: false }">
                <button type="button"
                        @click="open = !open"
                        class="w-full px-6 py-4 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">2. 契約書本文</h3>
                    <svg :class="open ? 'rotate-180' : ''"
                         class="w-4 h-4 text-gray-400 transition-transform"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="px-6 py-5 prose prose-sm max-w-none border-t border-gray-100">
                    {!! $contract->body !!}
                </div>
            </div>
            @endif

            {{-- Section 2: Related project / customer --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">3. 関連案件・顧客情報</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('companies.show', $contract->project->company) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $contract->project->company->company_name }}
                                </a>
                                @if ($contract->project->company->contact_name)
                                    <span class="text-gray-400 ml-1">（{{ $contract->project->company->contact_name }}）</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">案件</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('projects.show', $contract->project) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $contract->project->title }}
                                </a>
                                <span class="ml-2">
                                    <x-status-badge :status="$contract->project->status" type="project" />
                                </span>
                            </dd>
                        </div>
                        @if ($contract->project->company->email)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客メール</dt>
                                <dd class="mt-1 text-sm">
                                    <a href="mailto:{{ $contract->project->company->email }}"
                                       class="text-indigo-600 hover:underline">
                                        {{ $contract->project->company->email }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if ($contract->project->company->phone)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">顧客電話</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $contract->project->company->phone }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Section 3: Electronic consent --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border-l-4
                {{ $contract->status === 'signed' ? 'border-green-500' : ($contract->sign_token ? 'border-violet-500' : 'border-gray-300') }}">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">3. 電子同意</h3>
                </div>
                <div class="p-6">

                    @if ($contract->status === 'signed')
                        {{-- Signed: show signer details --}}
                        <div class="mb-4 flex items-center gap-2 text-green-700 text-sm font-medium">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            締結済み
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">同意者氏名</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $contract->signer_name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">同意者メール</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $contract->signer_email ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">同意日時</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $contract->signed_at?->format('Y年m月d日 H:i') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">同意者IP</dt>
                                <dd class="mt-1 text-sm text-gray-500 font-mono">{{ $contract->signer_ip_address ?? '—' }}</dd>
                            </div>
                        </dl>

                    @elseif ($contract->sign_token)
                        {{-- Token issued: show URL + mail button --}}
                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium text-gray-700">同意URL</p>
                                <span class="text-xs text-gray-400">
                                    有効期限：{{ $contract->sign_token_expires_at->format('Y/m/d H:i') }}
                                </span>
                            </div>
                            <div x-data="{ copied: false }" class="flex items-center gap-2">
                                <input type="text"
                                       readonly
                                       value="{{ route('sign.contracts.show', $contract->sign_token) }}"
                                       class="flex-1 text-xs border-gray-300 rounded-md bg-gray-50 font-mono text-gray-600 px-3 py-2 focus:outline-none"
                                       onclick="this.select()">
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ route('sign.contracts.show', $contract->sign_token) }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                        class="px-3 py-2 text-sm font-medium rounded-md border transition-colors whitespace-nowrap"
                                        :class="copied ? 'bg-green-100 border-green-300 text-green-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'">
                                    <span x-show="!copied">コピー</span>
                                    <span x-show="copied" x-cloak>コピー済</span>
                                </button>
                            </div>
                        </div>

                        {{-- Mail send button --}}
                        @if ($contract->project->company->email)
                            <div class="mb-4 flex items-center gap-3 p-3 bg-violet-50 border border-violet-200 rounded-md">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-violet-800">メール送信先</p>
                                    <p class="text-sm text-violet-700 truncate">{{ $contract->project->company->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('contracts.mail.send-sign-request', $contract) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-md hover:bg-violet-700 whitespace-nowrap">
                                        メール送信
                                    </button>
                                </form>
                            </div>
                        @else
                            <p class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-md px-3 py-2 mb-4">
                                顧客のメールアドレスが未登録のため、メール送信できません。
                            </p>
                        @endif

                        <form method="POST" action="{{ route('contracts.sign-url.generate', $contract) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="text-xs text-gray-400 hover:text-gray-600">
                                URLを再発行する（現在のURLは無効になります）
                            </button>
                        </form>

                    @else
                        {{-- No token yet --}}
                        <p class="text-sm text-gray-500 mb-4">
                            同意URLを発行すると、ログイン不要で相手が契約内容を確認・同意できます。
                        </p>

                        @if ($contract->project->company->email)
                            <div class="mb-4 flex items-center gap-3 p-3 bg-violet-50 border border-violet-200 rounded-md">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-violet-800">メール送信先</p>
                                    <p class="text-sm text-violet-700 truncate">{{ $contract->project->company->email }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-md px-3 py-2 mb-4">
                                顧客のメールアドレスが未登録のため、メール送信できません。
                            </p>
                        @endif

                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('contracts.sign-url.generate', $contract) }}">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 bg-white border border-violet-400 text-violet-700 text-sm font-medium rounded-md hover:bg-violet-50">
                                    URLのみ発行
                                </button>
                            </form>
                            @if ($contract->project->company->email)
                                <form method="POST" action="{{ route('contracts.mail.send-sign-request', $contract) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-md hover:bg-violet-700">
                                        URLを発行してメール送信
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('contracts.sign-url.generate', $contract) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-md hover:bg-violet-700">
                                        同意URLを発行する
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

            {{-- Section 4: Latest PDF --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border-l-4 {{ $latestPdf ? 'border-emerald-500' : 'border-gray-300' }}">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">4. 最新の契約PDF</h3>
                </div>

                @if ($latestPdf)
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 mb-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">ファイル名</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $latestPdf->file_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">生成日時</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $latestPdf->created_at->format('Y/m/d H:i') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">SHA256</dt>
                                <dd class="mt-1 text-xs text-gray-500 font-mono break-all">{{ $latestPdf->file_hash ?? '—' }}</dd>
                            </div>
                        </dl>
                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('contracts.files.download', [$contract, $latestPdf]) }}"
                               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                ダウンロード
                            </a>
                            <form method="POST"
                                  action="{{ route('contracts.files.verify-hash', [$contract, $latestPdf]) }}">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                                    ハッシュ検証
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-sm text-gray-400">
                        PDFはまだ生成されていません。同意完了後に自動生成されます。
                    </div>
                @endif
            </div>

            {{-- Section 5: All contract files --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        6. 契約ファイル一覧
                        <span class="ml-2 text-gray-400 font-normal normal-case">{{ $contract->files->count() }} 件</span>
                    </h3>
                    <div class="flex items-center gap-4">
                        {{-- ファイル一覧横のプレビューリンク（ヘッダーと同一機能のため無効化中 — 必要な場合はコメントを外す）
                        <a href="{{ route('contracts.pdf.preview', $contract) }}" target="_blank"
                           class="text-sm text-gray-500 hover:text-gray-800 font-medium">
                            プレビュー
                        </a>
                        --}}
                        {{-- PDF手動保存ボタン（無効化中 — 必要な場合はコメントを外す）
                        @unless (in_array($contract->status, ['signed', 'cancelled'], true))
                            <form method="POST" action="{{ route('contracts.pdf.store', $contract) }}">
                                @csrf
                                <button type="submit"
                                        class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                                    + PDFとして保存
                                </button>
                            </form>
                        @endunless
                        --}}
                    </div>
                </div>

                @if ($contract->files->isEmpty())
                    <div class="px-6 py-6 text-center text-sm text-gray-400">ファイルがありません。</div>
                @else
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ファイル名</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">生成日時</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SHA256（先頭16字）</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($contract->files as $file)
                                <tr class="hover:bg-gray-50 {{ $latestPdf && $file->id === $latestPdf->id ? 'bg-emerald-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                        {{ $file->file_name }}
                                        @if ($latestPdf && $file->id === $latestPdf->id)
                                            <span class="ml-2 text-xs text-emerald-600 font-sans font-medium">最新</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $file->created_at->format('Y/m/d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400 font-mono" title="{{ $file->file_hash }}">
                                        {{ $file->file_hash ? substr($file->file_hash, 0, 16) . '…' : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <a href="{{ route('contracts.files.download', [$contract, $file]) }}"
                                           class="text-indigo-600 hover:text-indigo-900">ダウンロード</a>
                                        <form method="POST"
                                              action="{{ route('contracts.files.verify-hash', [$contract, $file]) }}"
                                              class="inline">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-gray-800">検証</button>
                                        </form>
                                        @unless (in_array($contract->status, ['signed', 'cancelled'], true))
                                            <form method="POST"
                                                  action="{{ route('contracts.files.destroy', [$contract, $file]) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('このPDFファイルを削除します。よろしいですか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">削除</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>{{-- overflow-x-auto --}}
                @endif
            </div>

            {{-- Section 6: Evidence exports --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        7. 証跡エクスポート
                        <span class="ml-2 text-gray-400 font-normal normal-case">{{ $evidenceExports->count() }} 件</span>
                    </h3>
                    @if ($contract->status === 'signed')
                        <form method="POST" action="{{ route('contracts.evidence-exports.store', $contract) }}">
                            @csrf
                            <button type="submit"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                + 証跡ZIPを生成
                            </button>
                        </form>
                    @endif
                </div>

                <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 text-xs text-blue-700">
                    このZIPには契約PDF、監査ログ、メタデータ、ハッシュ情報が含まれます。
                    データベース障害時や第三者説明時に備え、必要に応じて外部保存してください。
                </div>

                @if ($evidenceExports->isEmpty())
                    <div class="px-6 py-6 text-center text-sm text-gray-400">
                        @if ($contract->status === 'signed')
                            証跡ZIPが生成されていません。「証跡ZIPを生成」から作成してください。
                        @else
                            証跡ZIPは締結済みの契約のみ生成できます。
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ファイル名</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">対象PDF</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">生成日時</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SHA256（先頭16字）</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($evidenceExports as $export)
                                <tr class="hover:bg-gray-50 {{ $latestEvidenceExport && $export->id === $latestEvidenceExport->id ? 'bg-indigo-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                        {{ $export->file_name }}
                                        @if ($latestEvidenceExport && $export->id === $latestEvidenceExport->id)
                                            <span class="ml-2 text-xs text-indigo-600 font-sans font-medium">最新</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if ($export->contractFile)
                                            <a href="{{ route('contracts.files.download', [$contract, $export->contractFile]) }}"
                                               class="text-indigo-600 hover:text-indigo-900 font-mono">
                                                {{ $export->contractFile->file_name }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $export->generated_at?->format('Y/m/d H:i') ?? $export->created_at->format('Y/m/d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400 font-mono" title="{{ $export->file_hash }}">
                                        {{ $export->file_hash ? substr($export->file_hash, 0, 16) . '…' : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('contracts.evidence-exports.download', [$contract, $export]) }}"
                                           class="text-indigo-600 hover:text-indigo-900">ダウンロード</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>{{-- overflow-x-auto --}}
                @endif
            </div>

            {{-- Section 7: Related invoices --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        8. 紐づく請求書
                        <span class="ml-2 text-gray-400 font-normal normal-case">{{ $contract->invoices->count() }} 件</span>
                    </h3>
                    <a href="{{ route('invoices.create', ['contract_id' => $contract->id]) }}"
                       class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700">
                        + この契約から請求書を作成
                    </a>
                </div>

                @if ($contract->invoices->isEmpty())
                    <div class="px-6 py-6 text-center text-sm text-gray-400">紐づく請求書はありません。</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">請求番号</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">税込金額</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">発行日</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($contract->invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-mono text-gray-700">{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ $invoice->title }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">{{ fmt_amount($invoice->total_amount) }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm">
                                            @php
                                                $badgeClass = match($invoice->status) {
                                                    'paid'      => 'bg-green-100 text-green-800',
                                                    'issued'    => 'bg-blue-100 text-blue-800',
                                                    'cancelled' => 'bg-gray-100 text-gray-500',
                                                    default     => 'bg-yellow-100 text-yellow-800',
                                                };
                                                $statusLabel = ['draft' => '下書き', 'issued' => '請求済', 'paid' => '入金済', 'cancelled' => 'キャンセル'][$invoice->status] ?? $invoice->status;
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $invoice->issued_at?->format('Y/m/d') ?? '—' }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-800">詳細 →</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Section 8: Audit logs --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                        9. 監査ログ
                        <span class="ml-2 text-gray-400 font-normal normal-case">最新{{ $auditLogs->count() }}件</span>
                    </h3>
                </div>

                @if ($auditLogs->isEmpty())
                    <div class="px-6 py-6 text-center text-sm text-gray-400">ログがありません。</div>
                @else
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日時</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">アクション</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ユーザーエージェント</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($auditLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-700">
                                        {{ $log->created_at->format('Y/m/d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-xs">
                                        @php $isWarning = str_contains($log->action, 'mismatch') || str_contains($log->action, 'missing') || str_contains($log->action, 'failed'); @endphp
                                        <span class="{{ $isWarning ? 'text-yellow-700 font-medium' : 'text-gray-700' }}">
                                            {{ $actionLabels[$log->action] ?? $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500 font-mono">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-xs text-gray-400 max-w-xs truncate" title="{{ $log->user_agent }}">
                                        {{ $log->user_agent ? Str::limit($log->user_agent, 60) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>{{-- overflow-x-auto --}}
                @endif
            </div>

        </div>
    </div>

    <script>
        // Two-step confirmation for contract deletion: a yes/no dialog,
        // then requiring the exact contract number to be typed before submitting.
        function confirmContractDelete(event, contractNumber) {
            if (!window.confirm('「' + contractNumber + '」を削除します。\nこの操作は取り消せません。本当によろしいですか？')) {
                return false;
            }

            const input = window.prompt(
                '削除を確定するには、契約番号を正確に入力してください。\n\n契約番号: ' + contractNumber
            );

            if (input !== contractNumber) {
                window.alert('契約番号が一致しなかったため、削除を中止しました。');
                return false;
            }

            return true;
        }
    </script>
</x-app-layout>
