<?php

namespace App\Services;

use App\Mail\ContractSignedNotificationMail;
use App\Mail\ContractSignRequestMail;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContractMailService
{
    public function __construct(
        private ContractSigningService $signingService,
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Send the consent URL to the contract's company email.
     * Issues a sign token first if one has not been generated yet.
     *
     * @throws \RuntimeException When the company has no email address.
     */
    public function sendSignRequest(Contract $contract, Request $request): void
    {
        if (! $contract->project->company->email) {
            throw new \RuntimeException('送信先メールアドレスが登録されていません。');
        }

        // Auto-issue a token when none exists yet
        if (! $contract->sign_token) {
            $contract = $this->signingService->generateSignUrl($contract, $request);
        }

        $signUrl = route('sign.contracts.show', $contract->sign_token);

        Mail::to($contract->project->company->email)
            ->send(new ContractSignRequestMail($contract, $signUrl));

        $this->auditLogService->log(
            action: 'contract_sign_request_mail_sent',
            targetType: 'contract',
            targetId: $contract->id,
            request: $request,
        );
    }

    /**
     * Notify the admin that a contract has been signed.
     * Errors are caught internally — mail failure must not interrupt signing.
     */
    public function sendSignedNotification(Contract $contract, Request $request): void
    {
        try {
            $adminEmail = config('mail.contract_admin') ?: config('mail.from.address');

            Mail::to($adminEmail)
                ->send(new ContractSignedNotificationMail($contract));

            $this->auditLogService->log(
                action: 'contract_signed_notification_mail_sent',
                targetType: 'contract',
                targetId: $contract->id,
                request: $request,
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send contract signed notification mail', [
                'contract_id' => $contract->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
