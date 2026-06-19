<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceFile;
use App\Services\InvoiceFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceFileController extends Controller
{
    public function __construct(private InvoiceFileService $service) {}

    /**
     * Generate a PDF for the given invoice and persist it.
     */
    public function store(Invoice $invoice, Request $request)
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled'], true),
            403,
            '入金済み・キャンセル済みの請求書のPDFは再生成できません。',
        );

        $this->service->generateAndStore($invoice, $request);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', '請求書PDFを生成しました。');
    }

    /**
     * Stream an invoice preview PDF inline without saving.
     */
    public function preview(Invoice $invoice)
    {
        $pdfContent = $this->service->preview($invoice);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
        ]);
    }

    /**
     * Stream a receipt preview PDF inline without saving.
     */
    public function previewReceipt(Invoice $invoice)
    {
        $pdfContent = $this->service->previewReceipt($invoice);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receipt-preview.pdf"',
        ]);
    }

    /**
     * Stream the stored invoice PDF as a download.
     */
    public function download(Invoice $invoice, InvoiceFile $invoiceFile)
    {
        abort_unless($invoiceFile->invoice_id === $invoice->id, 404);

        abort_unless(
            Storage::disk('invoices')->exists($invoiceFile->file_path),
            404,
            'ファイルが見つかりません。',
        );

        return Storage::disk('invoices')->download(
            $invoiceFile->file_path,
            $invoiceFile->file_name,
        );
    }
}
