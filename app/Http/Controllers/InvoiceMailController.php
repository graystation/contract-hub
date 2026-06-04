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
}
