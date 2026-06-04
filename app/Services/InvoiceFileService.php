<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceFileService
{
    public function __construct(private AuditLogService $auditLogService) {}

    /**
     * Generate an invoice PDF, persist it to storage, and record the file with its SHA256 hash.
     */
    public function generateAndStore(Invoice $invoice, Request $request): InvoiceFile
    {
        $invoice->loadMissing('project.company', 'payments');

        $pdfContent = $this->generatePdfContent($invoice);

        $fileName = $this->buildFileName($invoice);

        Storage::disk('invoices')->put($fileName, $pdfContent);

        $fileHash = hash('sha256', $pdfContent);

        $invoiceFile = InvoiceFile::create([
            'invoice_id' => $invoice->id,
            'file_name'  => $fileName,
            'file_path'  => $fileName,
            'file_type'  => 'pdf',
            'file_hash'  => $fileHash,
        ]);

        $this->auditLogService->log(
            action: 'invoice_pdf_generated',
            targetType: 'invoice',
            targetId: $invoice->id,
            request: $request,
        );

        return $invoiceFile;
    }

    /**
     * Return the most recently generated PDF for the given invoice, or null.
     */
    public function getLatestPdf(Invoice $invoice): ?InvoiceFile
    {
        return $invoice->files()
            ->where('file_type', 'pdf')
            ->latest()
            ->first();
    }

    /**
     * Render the invoice Blade template through dompdf and return the raw PDF bytes.
     */
    private function generatePdfContent(Invoice $invoice): string
    {
        return Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait')
            ->output();
    }

    /**
     * Build a unique filename from the invoice number and current timestamp.
     */
    private function buildFileName(Invoice $invoice): string
    {
        return $invoice->invoice_number . '_' . now()->format('YmdHis') . '.pdf';
    }
}
