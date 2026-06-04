@props(['invoice'])

@php
if ($invoice->is_overpaid) {
    $classes = 'bg-yellow-100 text-yellow-800';
    $label   = '過入金';
} elseif ($invoice->is_overdue) {
    $classes = 'bg-red-100 text-red-800';
    $label   = '期限超過';
} elseif ($invoice->status === 'paid' || $invoice->is_fully_paid) {
    $classes = 'bg-green-100 text-green-800';
    $label   = '入金済';
} elseif ($invoice->is_partially_paid) {
    $classes = 'bg-blue-100 text-blue-800';
    $label   = '一部入金';
} elseif ($invoice->status === 'cancelled') {
    $classes = 'bg-red-100 text-red-800';
    $label   = 'キャンセル';
} elseif ($invoice->status === 'draft') {
    $classes = 'bg-gray-100 text-gray-800';
    $label   = '下書き';
} else {
    // issued with no payment
    $classes = 'bg-orange-100 text-orange-800';
    $label   = '未入金';
}
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classes }}">
    {{ $label }}
</span>
