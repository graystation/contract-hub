<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\ContractFile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ContractFileService
{
    public const VERIFY_MATCHED    = 'matched';
    public const VERIFY_MISMATCHED = 'mismatched';
    public const VERIFY_MISSING    = 'missing';

    public function __construct(
        private PdfService $pdfService,
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Generate a PDF, persist it to storage, record the file and audit log.
     */
    public function generateAndStore(Contract $contract, Request $request): ContractFile
    {
        $pdfContent = $this->pdfService->generateContractPdf($contract);

        $fileName = $this->buildFileName($contract);
        $filePath = $fileName;

        Storage::disk('contracts')->put($filePath, $pdfContent);

        $fileHash = hash('sha256', $pdfContent);

        $contractFile = ContractFile::create([
            'contract_id' => $contract->id,
            'file_name'   => $fileName,
            'file_path'   => $filePath,
            'file_type'   => 'pdf',
            'file_hash'   => $fileHash,
        ]);

        $this->auditLogService->log(
            action: 'contract_pdf_generated',
            targetType: 'contract',
            targetId: $contract->id,
            request: $request,
        );

        return $contractFile;
    }

    /**
     * Generate a PDF for preview without persisting it to storage.
     */
    public function preview(Contract $contract): string
    {
        return $this->pdfService->generateContractPdf($contract);
    }

    /**
     * Delete a stored PDF file and its record.
     */
    public function deleteFile(ContractFile $contractFile, Request $request): void
    {
        Storage::disk('contracts')->delete($contractFile->file_path);

        $contractId = $contractFile->contract_id;

        $contractFile->delete();

        $this->auditLogService->log(
            action: 'contract_file_deleted',
            targetType: 'contract',
            targetId: $contractId,
            request: $request,
        );
    }

    /**
     * Return the most recently created PDF for the contract, or null if none.
     */
    public function getLatestPdf(Contract $contract): ?ContractFile
    {
        return $contract->files()
            ->where('file_type', 'pdf')
            ->latest()
            ->first();
    }

    /**
     * Verify that the file on disk still matches the stored SHA256 hash.
     * Returns one of the VERIFY_* constants.
     */
    public function verifyHash(ContractFile $contractFile, Request $request): string
    {
        if (!Storage::disk('contracts')->exists($contractFile->file_path)) {
            $this->auditLogService->log(
                action: 'contract_file_hash_verified_missing',
                targetType: 'contract_file',
                targetId: $contractFile->id,
                request: $request,
            );

            return self::VERIFY_MISSING;
        }

        $currentHash = hash('sha256', Storage::disk('contracts')->get($contractFile->file_path));
        $result      = $currentHash === $contractFile->file_hash
            ? self::VERIFY_MATCHED
            : self::VERIFY_MISMATCHED;

        $this->auditLogService->log(
            action: "contract_file_hash_verified_{$result}",
            targetType: 'contract_file',
            targetId: $contractFile->id,
            request: $request,
        );

        return $result;
    }

    /**
     * Check whether the physical file exists on the contracts disk.
     */
    public function fileExists(ContractFile $contractFile): bool
    {
        return Storage::disk('contracts')->exists($contractFile->file_path);
    }

    /**
     * Fetch the latest 20 audit log entries related to a contract and its files.
     */
    public function getRelatedAuditLogs(Contract $contract): Collection
    {
        $fileIds = $contract->files->pluck('id');

        return AuditLog::where(function ($q) use ($contract, $fileIds) {
            $q->where('target_type', 'contract')
              ->where('target_id', $contract->id);

            if ($fileIds->isNotEmpty()) {
                $q->orWhere(function ($q2) use ($fileIds) {
                    $q2->where('target_type', 'contract_file')
                       ->whereIn('target_id', $fileIds);
                });
            }
        })
        ->with('user')
        ->latest()
        ->limit(20)
        ->get();
    }

    /**
     * Build a unique filename using contract number and timestamp.
     */
    private function buildFileName(Contract $contract): string
    {
        $timestamp = now()->format('YmdHis');

        return "{$contract->contract_number}_{$timestamp}.pdf";
    }
}
