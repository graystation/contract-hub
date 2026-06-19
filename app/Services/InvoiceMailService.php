<?php

namespace App\Services;

use App\Mail\InvoiceReceiptMail;
use App\Mail\InvoiceSendMail;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceMailService
{
    public function __construct(
        private InvoiceFileService $fileService,
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Send the invoice PDF to the customer.
     * Auto-generates a PDF if no InvoiceFile exists yet.
     *
     * @throws \RuntimeException When the company has no email, or when mail sending fails.
     */
    public function sendInvoice(Invoice $invoice, Request $request): void
    {
        $invoice->loadMissing('project.company', 'payments', 'files');

        if (! $invoice->project->company->email) {
            throw new \RuntimeException('送信先メールアドレスが登録されていません。');
        }

        // Always regenerate PDF to ensure latest content is attached
        $invoiceFile = $this->fileService->generateAndStore($invoice, $request);

        try {
            Mail::to($invoice->project->company->email)
                ->send(new InvoiceSendMail($invoice, $invoiceFile));

            $this->auditLogService->log(
                action: 'invoice_mail_sent',
                targetType: 'invoice',
                targetId: $invoice->id,
                request: $request,
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send invoice mail', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'mail_type'      => InvoiceSendMail::class,
                'error'          => $e->getMessage(),
            ]);

            try {
                $this->auditLogService->log(
                    action: 'invoice_mail_failed',
                    targetType: 'invoice',
                    targetId: $invoice->id,
                    request: $request,
                );
            } catch (\Throwable $auditE) {
                Log::error('Failed to create failure audit log for invoice mail', [
                    'invoice_id' => $invoice->id,
                    'error'      => $auditE->getMessage(),
                ]);
            }

            throw new \RuntimeException('メール送信に失敗しました。');
        }
    }

    /**
     * Generate a receipt PDF and send it to the customer.
     *
     * @throws \RuntimeException When the company has no email, or when mail sending fails.
     */
    public function sendReceipt(Invoice $invoice, Request $request): void
    {
        $invoice->loadMissing('project.company', 'payments', 'files');

        if (! $invoice->project->company->email) {
            throw new \RuntimeException('送信先メールアドレスが登録されていません。');
        }

        $receiptFile = $this->fileService->generateAndStoreReceipt($invoice, $request);

        try {
            Mail::to($invoice->project->company->email)
                ->send(new InvoiceReceiptMail($invoice, $receiptFile));

            $this->auditLogService->log(
                action: 'invoice_receipt_mail_sent',
                targetType: 'invoice',
                targetId: $invoice->id,
                request: $request,
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send invoice receipt mail', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'mail_type'      => InvoiceReceiptMail::class,
                'error'          => $e->getMessage(),
            ]);

            try {
                $this->auditLogService->log(
                    action: 'invoice_receipt_mail_failed',
                    targetType: 'invoice',
                    targetId: $invoice->id,
                    request: $request,
                );
            } catch (\Throwable $auditE) {
                Log::error('Failed to create failure audit log for invoice receipt mail', [
                    'invoice_id' => $invoice->id,
                    'error'      => $auditE->getMessage(),
                ]);
            }

            throw new \RuntimeException('領収書メール送信に失敗しました。');
        }
    }
}
