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
            ->with('success', 'PDFを生成しました。');
    }

    /**
     * Stream the stored PDF file as a download.
     */
    public function download(Contract $contract, ContractFile $contractFile)
    {
        abort_unless(
            $contractFile->contract_id === $contract->id,
            404,
        );

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
}
