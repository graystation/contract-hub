<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContractSigningService
{
    /** Days until a sign token expires. */
    private const TOKEN_EXPIRY_DAYS = 14;

    public function __construct(
        private ContractFileService $fileService,
        private AuditLogService $auditLogService,
        private ContractEvidenceExportService $evidenceExportService,
    ) {}

    /**
     * Issue a new sign token for the contract and transition status to 'sent'.
     */
    public function generateSignUrl(Contract $contract, Request $request): Contract
    {
        $contract->update([
            'sign_token'            => Str::random(64),
            'sign_token_expires_at' => now()->addDays(self::TOKEN_EXPIRY_DAYS),
            'status'                => 'sent',
        ]);

        $this->auditLogService->log(
            action: 'contract_sign_url_generated',
            targetType: 'contract',
            targetId: $contract->id,
            request: $request,
        );

        return $contract->fresh();
    }

    /**
     * Find a contract by its sign token (regardless of validity).
     * Returns null when the token does not exist.
     */
    public function findByToken(string $token): ?Contract
    {
        return Contract::where('sign_token', $token)
            ->with('project.company')
            ->first();
    }

    /**
     * Return the validity state of a contract's token.
     * Possible values: 'valid' | 'expired' | 'signed' | 'signed_expired' | 'cancelled'
     */
    public function getTokenStatus(Contract $contract): string
    {
        if ($contract->status === 'signed') {
            if ($this->isDownloadExpired($contract)) {
                return 'signed_expired';
            }

            return 'signed';
        }

        if ($contract->status === 'cancelled') {
            return 'cancelled';
        }

        if ($contract->sign_token_expires_at && $contract->sign_token_expires_at->isPast()) {
            return 'expired';
        }

        return 'valid';
    }

    /**
     * Whether a signed contract's PDF download link has passed its expiry
     * (TOKEN_EXPIRY_DAYS days after signed_at).
     */
    public function isDownloadExpired(Contract $contract): bool
    {
        return $contract->signed_at !== null
            && $contract->signed_at->addDays(self::TOKEN_EXPIRY_DAYS)->isPast();
    }

    /**
     * Record external user consent and regenerate the PDF with signer info.
     */
    public function sign(Contract $contract, array $validated, Request $request): void
    {
        $contract->update([
            'signer_name'       => $validated['signer_name'],
            'signer_email'      => $validated['signer_email'],
            'signer_address'    => $validated['signer_address'],
            'signer_ip_address' => $request->ip(),
            'signer_user_agent' => $request->userAgent(),
            'signed_at'         => now(),
            'status'            => 'signed',
        ]);

        $this->auditLogService->log(
            action: 'contract_signed_by_external_user',
            targetType: 'contract',
            targetId: $contract->id,
            request: $request,
        );

        // Re-generate PDF to capture signer details in the evidence document
        $contract->refresh()->load('project.company');
        $this->fileService->generateAndStore($contract, $request);

        // Auto-generate evidence ZIP at the moment of consent
        $contract->refresh()->load(['project.company', 'files']);
        $this->evidenceExportService->generateEvidenceZip($contract, $request);
    }
}
