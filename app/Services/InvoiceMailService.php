<?php

namespace App\Services;

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

        // Auto-generate PDF if no file exists yet
        $invoiceFile = $this->fileService->getLatestPdf($invoice);
        if (! $invoiceFile) {
            $invoiceFile = $this->fileService->generateAndStore($invoice, $request);
        }

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
}
