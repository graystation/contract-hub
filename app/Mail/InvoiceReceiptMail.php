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

class InvoiceReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly InvoiceFile $receiptFile,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('【領収書】%s %s', $this->invoice->invoice_number, $this->invoice->title),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice-receipt',
            with: [
                'invoice'     => $this->invoice,
                'company'     => $this->invoice->project->company,
                'project'     => $this->invoice->project,
                'receiptFile' => $this->receiptFile,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('invoices', $this->receiptFile->file_path)
                ->as($this->receiptFile->file_name)
                ->withMime('application/pdf'),
        ];
    }
}
