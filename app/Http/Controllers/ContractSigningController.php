<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractSigningService;
use Illuminate\Http\Request;

class ContractSigningController extends Controller
{
    public function __construct(private ContractSigningService $service) {}

    /**
     * Generate a sign token and transition the contract to 'sent'.
     */
    public function generate(Contract $contract, Request $request)
    {
        $this->service->generateSignUrl($contract, $request);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', '同意URLを発行しました。');
    }
}
