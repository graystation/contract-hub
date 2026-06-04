@props(['status', 'type' => 'project'])

@php
$map = [
    'project' => [
        'draft'     => ['classes' => 'bg-gray-100 text-gray-800',   'label' => '下書き'],
        'active'    => ['classes' => 'bg-green-100 text-green-800', 'label' => '進行中'],
        'completed' => ['classes' => 'bg-blue-100 text-blue-800',   'label' => '完了'],
        'cancelled' => ['classes' => 'bg-red-100 text-red-800',     'label' => 'キャンセル'],
    ],
    'contract' => [
        'draft'     => ['classes' => 'bg-gray-100 text-gray-800',    'label' => '下書き'],
        'sent'      => ['classes' => 'bg-yellow-100 text-yellow-800','label' => '送付済'],
        'signed'    => ['classes' => 'bg-green-100 text-green-800',  'label' => '締結済'],
        'cancelled' => ['classes' => 'bg-red-100 text-red-800',      'label' => 'キャンセル'],
    ],
    'invoice' => [
        'draft'     => ['classes' => 'bg-gray-100 text-gray-800',    'label' => '下書き'],
        'issued'    => ['classes' => 'bg-blue-100 text-blue-800',    'label' => '請求済'],
        'paid'      => ['classes' => 'bg-green-100 text-green-800',  'label' => '入金済'],
        'cancelled' => ['classes' => 'bg-red-100 text-red-800',      'label' => 'キャンセル'],
    ],
];

$config  = $map[$type][$status] ?? ['classes' => 'bg-gray-100 text-gray-800', 'label' => $status];
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['classes'] }}">
    {{ $config['label'] }}
</span>
