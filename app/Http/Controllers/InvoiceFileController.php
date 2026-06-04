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
        $this->service->generateAndStore($invoice, $request);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', '請求書PDFを生成しました。');
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
