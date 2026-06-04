<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractMailService;
use Illuminate\Http\Request;

class ContractMailController extends Controller
{
    public function __construct(private ContractMailService $service) {}

    /**
     * Send the consent URL to the company's registered email address.
     */
    public function sendSignRequest(Contract $contract, Request $request)
    {
        $contract->load('project.company');

        try {
            $this->service->sendSignRequest($contract, $request);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('contracts.show', $contract)
                ->with('warning', $e->getMessage());
        }

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', '同意URLメールを送信しました。');
    }
}
