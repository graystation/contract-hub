<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractFile;
use App\Services\ContractFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractFileController extends Controller
{
    public function __construct(private ContractFileService $service) {}

    /**
     * Generate a PDF for the given contract and persist it.
     */
    public function store(Contract $contract, Request $request)
    {
        $this->service->generateAndStore($contract, $request);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'PDFを生成して保存しました。');
    }

    /**
     * Generate a PDF for preview without persisting it.
     */
    public function preview(Contract $contract)
    {
        $pdfContent = $this->service->preview($contract);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
        ]);
    }

    /**
     * Delete a stored PDF file and its record.
     */
    public function destroy(Contract $contract, ContractFile $contractFile, Request $request)
    {
        abort_unless($contractFile->contract_id === $contract->id, 404);

        abort_if(
            in_array($contract->status, ['signed', 'cancelled'], true),
            403,
            '締結済み・キャンセル済みの契約のPDFは削除できません。',
        );

        $this->service->deleteFile($contractFile, $request);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'PDFファイルを削除しました。');
    }

    /**
     * Stream the stored PDF file as a download.
     */
    public function download(Contract $contract, ContractFile $contractFile)
    {
        abort_unless($contractFile->contract_id === $contract->id, 404);

        abort_unless(
            Storage::disk('contracts')->exists($contractFile->file_path),
            404,
            'ファイルが見つかりません。',
        );

        return Storage::disk('contracts')->download(
            $contractFile->file_path,
            $contractFile->file_name,
        );
    }

    /**
     * Re-compute SHA256 of the stored file and compare against the recorded hash.
     */
    public function verifyHash(Contract $contract, ContractFile $contractFile, Request $request)
    {
        abort_unless($contractFile->contract_id === $contract->id, 404);

        $result = $this->service->verifyHash($contractFile, $request);

        [$flashKey, $message] = match ($result) {
            ContractFileService::VERIFY_MATCHED    => ['success', 'このPDFは保存時から変更されていません。'],
            ContractFileService::VERIFY_MISMATCHED => ['warning', '警告：PDFファイルが保存時から変更されている可能性があります。'],
            ContractFileService::VERIFY_MISSING    => ['warning', '警告：PDFファイルが見つかりません。'],
        };

        return redirect()
            ->route('contracts.show', $contract)
            ->with($flashKey, $message);
    }
}
