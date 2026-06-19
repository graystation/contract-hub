<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceMailService;
use Illuminate\Http\Request;

class InvoiceMailController extends Controller
{
    public function __construct(private InvoiceMailService $service) {}

    /**
     * Send the invoice PDF to the customer's email address.
     */
    public function send(Invoice $invoice, Request $request)
    {
        try {
            $this->service->sendInvoice($invoice, $request);
        } catch (\RuntimeException $e) {
            $isMailFailure = str_contains($e->getMessage(), 'メール送信に失敗');

            return redirect()
                ->route('invoices.show', $invoice)
                ->with($isMailFailure ? 'error' : 'warning', $e->getMessage());
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', '請求書メールを送信しました。');
    }

    /**
     * Generate a receipt PDF and send it to the customer's email address.
     */
    public function sendReceipt(Invoice $invoice, Request $request)
    {
        abort_if($invoice->status !== 'paid', 403, '入金済みの請求書にのみ領収書を発行できます。');

        try {
            $this->service->sendReceipt($invoice, $request);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', '領収書メールを送信しました。');
    }
}
