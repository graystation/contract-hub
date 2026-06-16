<?php

namespace App\Services;

use App\Mail\ContractSignedCustomerMail;
use App\Mail\ContractSignedNotificationMail;
use App\Mail\ContractSignRequestMail;
use App\Models\Contract;
use App\Models\ContractFile;
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
     * @throws \RuntimeException When no company email exists, or when mail sending fails.
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

        try {
            Mail::to($contract->project->company->email)
                ->send(new ContractSignRequestMail($contract, $signUrl));

            $this->auditLogService->log(
                action: 'contract_sign_request_mail_sent',
                targetType: 'contract',
                targetId: $contract->id,
                request: $request,
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send contract sign request mail', [
                'contract_id'     => $contract->id,
                'contract_number' => $contract->contract_number,
                'mail_type'       => ContractSignRequestMail::class,
                'error'           => $e->getMessage(),
            ]);

            try {
                $this->auditLogService->log(
                    action: 'contract_sign_request_mail_failed',
                    targetType: 'contract',
                    targetId: $contract->id,
                    request: $request,
                );
            } catch (\Throwable $auditE) {
                Log::error('Failed to create failure audit log for sign request mail', [
                    'contract_id' => $contract->id,
                    'error'       => $auditE->getMessage(),
                ]);
            }

            // Re-throw so the controller can display an actionable error
            throw new \RuntimeException('メール送信に失敗しました。URLをコピーして手動で送信してください。');
        }
    }

    /**
     * Notify the admin that a contract has been signed.
     * All errors are caught internally — mail failure must not interrupt signing.
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
                'contract_id'     => $contract->id,
                'contract_number' => $contract->contract_number,
                'mail_type'       => ContractSignedNotificationMail::class,
                'error'           => $e->getMessage(),
            ]);

            try {
                $this->auditLogService->log(
                    action: 'contract_signed_notification_mail_failed',
                    targetType: 'contract',
                    targetId: $contract->id,
                    request: $request,
                );
            } catch (\Throwable $auditE) {
                Log::error('Failed to create failure audit log for notification mail', [
                    'contract_id' => $contract->id,
                    'error'       => $auditE->getMessage(),
                ]);
            }
        }
    }

    /**
     * Send the signed contract PDF to the external signer.
     * All errors are caught internally — mail failure must not interrupt signing.
     */
    public function sendSignedCustomerMail(Contract $contract, ContractFile $contractFile, Request $request): void
    {
        try {
            $adminEmail = config('mail.contract_admin') ?: config('mail.from.address');

            Mail::to($contract->signer_email)
                ->bcc($adminEmail)
                ->send(new ContractSignedCustomerMail($contract, $contractFile));

            $this->auditLogService->log(
                action: 'contract_signed_customer_mail_sent',
                targetType: 'contract',
                targetId: $contract->id,
                request: $request,
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send contract signed customer mail', [
                'contract_id'     => $contract->id,
                'contract_number' => $contract->contract_number,
                'mail_type'       => ContractSignedCustomerMail::class,
                'error'           => $e->getMessage(),
            ]);

            try {
                $this->auditLogService->log(
                    action: 'contract_signed_customer_mail_failed',
                    targetType: 'contract',
                    targetId: $contract->id,
                    request: $request,
                );
            } catch (\Throwable $auditE) {
                Log::error('Failed to create failure audit log for customer mail', [
                    'contract_id' => $contract->id,
                    'error'       => $auditE->getMessage(),
                ]);
            }
        }
    }
}
