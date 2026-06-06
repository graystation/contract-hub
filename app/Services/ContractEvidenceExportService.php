<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\ContractEvidenceExport;
use App\Models\ContractFile;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ContractEvidenceExportService
{
    public function __construct(
        private ContractFileService $contractFileService,
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Return all evidence exports for the given contract, newest first.
     */
    public function getExportsForContract(Contract $contract): Collection
    {
        return $contract->evidenceExports()->latest()->get();
    }

    /**
     * Return the most recently created export for the contract, or null if none.
     */
    public function getLatestExport(Contract $contract): ?ContractEvidenceExport
    {
        return $contract->evidenceExports()->latest()->first();
    }

    /**
     * Create a placeholder export record without generating any actual ZIP.
     */
    public function createPlaceholderExport(Contract $contract): ContractEvidenceExport
    {
        $timestamp = now()->format('YmdHis');
        $fileName  = "evidence_{$contract->contract_number}_{$timestamp}.zip";

        return ContractEvidenceExport::create([
            'contract_id'  => $contract->id,
            'file_name'    => $fileName,
            'file_path'    => $fileName,
            'file_hash'    => null,
            'generated_at' => now(),
        ]);
    }

    /**
     * Generate an evidence ZIP for the contract, persist it to the contract_evidence disk,
     * record a ContractEvidenceExport row, and write an audit log entry.
     */
    public function generateEvidenceZip(Contract $contract, Request $request): ContractEvidenceExport
    {
        $contract->loadMissing(['project.company', 'files']);

        // Get or auto-generate the latest contract PDF
        $latestPdf = $this->contractFileService->getLatestPdf($contract);
        if ($latestPdf === null) {
            $latestPdf = $this->contractFileService->generateAndStore($contract, $request);
        }

        $generatedAt = now();

        $pdfContent      = Storage::disk('contracts')->get($latestPdf->file_path);
        $auditCsvContent = $this->buildAuditCsv($contract);
        $metadataContent = $this->buildMetadata($contract, $latestPdf, $generatedAt);

        $pdfHash      = hash('sha256', $pdfContent);
        $csvHash      = hash('sha256', $auditCsvContent);
        $metadataHash = hash('sha256', $metadataContent);

        $hashTxtContent = $this->buildHashTxt($pdfHash, $csvHash, $metadataHash, $generatedAt);

        $zipContent = $this->buildZip($pdfContent, $auditCsvContent, $metadataContent, $hashTxtContent);

        $zipHash  = hash('sha256', $zipContent);
        $fileName = $this->buildZipFileName($contract, $generatedAt);

        Storage::disk('contract_evidence')->put($fileName, $zipContent);

        $export = ContractEvidenceExport::create([
            'contract_id'  => $contract->id,
            'file_name'    => $fileName,
            'file_path'    => $fileName,
            'file_hash'    => $zipHash,
            'generated_at' => $generatedAt,
        ]);

        $this->auditLogService->log(
            action:     'contract_evidence_export_generated',
            targetType: 'contract',
            targetId:   $contract->id,
            request:    $request,
        );

        return $export;
    }

    private function buildZipFileName(Contract $contract, CarbonInterface $generatedAt): string
    {
        return "{$contract->contract_number}_evidence_{$generatedAt->format('YmdHis')}.zip";
    }

    /**
     * Write four files to a temp directory, zip them, return the raw ZIP bytes, then clean up.
     */
    private function buildZip(
        string $pdfContent,
        string $auditCsvContent,
        string $metadataContent,
        string $hashTxtContent,
    ): string {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('evidence_', true);
        mkdir($tempDir, 0700, true);

        try {
            file_put_contents($tempDir . '/contract.pdf',  $pdfContent);
            file_put_contents($tempDir . '/audit-log.csv', $auditCsvContent);
            file_put_contents($tempDir . '/metadata.json', $metadataContent);
            file_put_contents($tempDir . '/hash.txt',      $hashTxtContent);

            $zipPath = $tempDir . '/archive.zip';
            $zip     = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFile($tempDir . '/contract.pdf',  'contract.pdf');
            $zip->addFile($tempDir . '/audit-log.csv', 'audit-log.csv');
            $zip->addFile($tempDir . '/metadata.json', 'metadata.json');
            $zip->addFile($tempDir . '/hash.txt',      'hash.txt');
            $zip->close();

            return file_get_contents($zipPath);
        } finally {
            foreach (['contract.pdf', 'audit-log.csv', 'metadata.json', 'hash.txt', 'archive.zip'] as $f) {
                @unlink($tempDir . DIRECTORY_SEPARATOR . $f);
            }
            @rmdir($tempDir);
        }
    }

    private function buildAuditCsv(Contract $contract): string
    {
        $fileIds = $contract->files->pluck('id');

        $logs = AuditLog::where(function ($q) use ($contract, $fileIds) {
            $q->where('target_type', 'contract')->where('target_id', $contract->id);
            if ($fileIds->isNotEmpty()) {
                $q->orWhere(function ($q2) use ($fileIds) {
                    $q2->where('target_type', 'contract_file')
                       ->whereIn('target_id', $fileIds);
                });
            }
        })->orderBy('created_at')->get();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['created_at', 'action', 'target_type', 'target_id', 'ip_address', 'user_agent']);

        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->created_at->toIso8601String(),
                $log->action,
                $log->target_type,
                $log->target_id,
                $log->ip_address ?? '',
                $log->user_agent ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    private function buildMetadata(
        Contract $contract,
        ContractFile $contractFile,
        CarbonInterface $generatedAt,
    ): string {
        $project = $contract->project;
        $company = $project->company;

        return json_encode([
            'contract_id'            => $contract->id,
            'contract_number'        => $contract->contract_number,
            'project_title'          => $project->title,
            'company_name'           => $company->company_name,
            'signer_name'            => $contract->signer_name,
            'signer_email'           => $contract->signer_email,
            'signed_at'              => $contract->signed_at?->toIso8601String(),
            'signer_ip_address'      => $contract->signer_ip_address,
            'signer_user_agent'      => $contract->signer_user_agent,
            'contract_pdf_file_name' => $contractFile->file_name,
            'contract_pdf_hash'      => $contractFile->file_hash,
            'evidence_generated_at'  => $generatedAt->toIso8601String(),
            'app_url'                => config('app.url'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function buildHashTxt(
        string $pdfHash,
        string $csvHash,
        string $metadataHash,
        CarbonInterface $generatedAt,
    ): string {
        return implode("\n", [
            "contract.pdf    SHA256: {$pdfHash}",
            "audit-log.csv   SHA256: {$csvHash}",
            "metadata.json   SHA256: {$metadataHash}",
            '',
            "Generated at: {$generatedAt->toIso8601String()}",
            'APP_URL:  ' . config('app.url'),
            'APP_NAME: ' . config('app.name'),
        ]);
    }
}
