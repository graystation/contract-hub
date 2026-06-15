<?php

namespace App\Services;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function __construct(
        private ContractBodyService $bodyService,
    ) {}

    /**
     * Generate PDF content for a contract.
     * Returns the raw PDF binary string.
     */
    public function generateContractPdf(Contract $contract): string
    {
        $contract->loadMissing('project.company');

        // Work on a clone so the resolved body (with signer/operator info filled in)
        // is used only for rendering and never persisted back to the contract record.
        $renderContract = clone $contract;
        $renderContract->body = $this->bodyService->resolve($contract->body ?? '', $contract);

        $pdf = Pdf::loadView('contracts.pdf', ['contract' => $renderContract])
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
