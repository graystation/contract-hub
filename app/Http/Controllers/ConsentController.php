<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsentRequest;
use App\Services\ContractSigningService;

class ConsentController extends Controller
{
    public function __construct(private ContractSigningService $service) {}

    /**
     * Show the external contract consent page.
     */
    public function show(string $token)
    {
        $contract = $this->service->findByToken($token);

        if (! $contract) {
            return view('sign.invalid', ['reason' => 'not_found']);
        }

        $status = $this->service->getTokenStatus($contract);

        if ($status !== 'valid') {
            return view('sign.invalid', ['reason' => $status, 'contract' => $contract]);
        }

        return view('sign.show', compact('contract', 'token'));
    }

    /**
     * Process the external user's consent.
     */
    public function store(string $token, ConsentRequest $request)
    {
        $contract = $this->service->findByToken($token);

        if (! $contract) {
            return view('sign.invalid', ['reason' => 'not_found']);
        }

        $status = $this->service->getTokenStatus($contract);

        if ($status !== 'valid') {
            return view('sign.invalid', ['reason' => $status, 'contract' => $contract]);
        }

        $this->service->sign($contract, $request->validated(), $request);

        return view('sign.complete', [
            'contract' => $contract->fresh(),
            'signedAt' => now(),
        ]);
    }
}
