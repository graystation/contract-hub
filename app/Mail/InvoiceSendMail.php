<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\InvoiceFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSendMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly InvoiceFile $invoiceFile,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('【請求書】%s %s', $this->invoice->invoice_number, $this->invoice->title),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice-send',
            with: [
                'invoice'     => $this->invoice,
                'company'     => $this->invoice->project->company,
                'project'     => $this->invoice->project,
                'invoiceFile' => $this->invoiceFile,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('invoices', $this->invoiceFile->file_path)
                ->as($this->invoiceFile->file_name)
                ->withMime('application/pdf'),
        ];
    }
}
