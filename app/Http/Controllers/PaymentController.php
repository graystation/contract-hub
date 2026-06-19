<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceFileService;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $service,
        private InvoiceFileService $fileService,
    ) {}

    public function store(Invoice $invoice, PaymentRequest $request)
    {
        $this->service->create($invoice, $request->validated(), $request);

        return redirect()->route('invoices.show', $invoice)->with('success', '入金を登録しました。');
    }

    public function edit(Payment $payment)
    {
        $payment->load('invoice.project.company', 'invoice.payments', 'invoice.files', 'invoice.contract');

        $invoice = $payment->invoice;

        return view('invoices.show', [
            'invoice'        => $invoice,
            'editingPayment' => $payment,
            'latestPdf'      => $this->fileService->getLatestPdf($invoice),
            'latestReceipt'  => $this->fileService->getLatestReceipt($invoice),
            'receipts'       => $this->fileService->getReceipts($invoice),
            'auditLogs'      => collect(),
        ]);
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        $invoice = $payment->invoice;
        $this->service->update($payment, $request->validated(), $request);

        return redirect()->route('invoices.show', $invoice)->with('success', '入金情報を更新しました。');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $this->service->delete($payment, request());

        return redirect()->route('invoices.show', $invoice)->with('success', '入金を削除しました。');
    }
}
