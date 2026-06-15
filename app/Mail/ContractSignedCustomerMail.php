<?php

namespace App\Mail;

use App\Models\Contract;
use App\Models\ContractFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignedCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Contract $contract,
        public readonly ContractFile $contractFile,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('【契約締結完了】%s', $this->contract->contract_number),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contract-signed-customer',
            with: [
                'contract' => $this->contract,
                'company'  => $this->contract->project->company,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('contracts', $this->contractFile->file_path)
                ->as($this->contractFile->file_name)
                ->withMime('application/pdf'),
        ];
    }
}
