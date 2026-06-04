<?php

namespace App\Services;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Generate PDF content for a contract.
     * Returns the raw PDF binary string.
     */
    public function generateContractPdf(Contract $contract): string
    {
        $contract->loadMissing('project.company');

        $pdf = Pdf::loadView('contracts.pdf', compact('contract'))
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
