<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ContractSignedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Contract $contract,
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
            markdown: 'mail.contract-signed-notification',
            with: [
                'contract'   => $this->contract,
                'company'    => $this->contract->project->company,
                'project'    => $this->contract->project,
                'adminUrl'   => route('contracts.show', $this->contract),
            ],
        );
    }

    public function attachments(): array
    {
        $latestFile = $this->contract->files()->latest()->first();

        if ($latestFile === null) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('contracts', $latestFile->file_path)
                ->as($latestFile->file_name)
                ->withMime('application/pdf'),
        ];
    }
}
