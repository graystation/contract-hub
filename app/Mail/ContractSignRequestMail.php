<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Contract $contract,
        public readonly string $signUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('【契約確認のお願い】%s', $this->contract->contract_number),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contract-sign-request',
            with: [
                'contract'    => $this->contract,
                'signUrl'     => $this->signUrl,
                'expiresAt'   => $this->contract->sign_token_expires_at,
                'company'     => $this->contract->project->company,
                'project'     => $this->contract->project,
            ],
        );
    }
}
