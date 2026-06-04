<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractFileService
{
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
     * Build a unique filename using contract number and timestamp.
     */
    private function buildFileName(Contract $contract): string
    {
        $timestamp = now()->format('YmdHis');

        return "{$contract->contract_number}_{$timestamp}.pdf";
    }
}
