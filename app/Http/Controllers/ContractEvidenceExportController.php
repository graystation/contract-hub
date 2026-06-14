<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractEvidenceExport;
use App\Services\ContractEvidenceExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractEvidenceExportController extends Controller
{
    public function __construct(private ContractEvidenceExportService $service) {}

    /**
     * Generate a new evidence ZIP for the given contract.
     */
    public function store(Contract $contract, Request $request)
    {
        abort_unless(
            $contract->status === 'signed',
            403,
            '締結済みの契約のみ証跡ZIPを生成できます。',
        );

        try {
            $this->service->generateEvidenceZip($contract, $request);
        } catch (\Throwable $e) {
            Log::error('ContractEvidenceExport generation failed', [
                'contract_id' => $contract->id,
                'error'       => $e->getMessage(),
            ]);

            return redirect()
                ->route('contracts.show', $contract)
                ->with('error', '証跡ZIPの生成に失敗しました。');
        }

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', '証跡ZIPを生成しました。');
    }

    /**
     * Stream the evidence ZIP as a download response.
     */
    public function download(Contract $contract, ContractEvidenceExport $export)
    {
        abort_unless($export->contract_id === $contract->id, 404);

        abort_unless(
            Storage::disk('contract_evidence')->exists($export->file_path),
            404,
            'ファイルが見つかりません。',
        );

        return Storage::disk('contract_evidence')->download(
            $export->file_path,
            $export->file_name,
        );
    }
}
