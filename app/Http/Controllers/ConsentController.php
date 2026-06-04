<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsentRequest;
use App\Services\ContractMailService;
use App\Services\ContractSigningService;
use Illuminate\Support\Facades\Log;

class ConsentController extends Controller
{
    public function __construct(
        private ContractSigningService $signingService,
        private ContractMailService $mailService,
    ) {}

    /**
     * Show the external contract consent page.
     */
    public function show(string $token)
    {
        $contract = $this->signingService->findByToken($token);

        if (! $contract) {
            return view('sign.invalid', ['reason' => 'not_found']);
        }

        $status = $this->signingService->getTokenStatus($contract);

        if ($status !== 'valid') {
            return view('sign.invalid', ['reason' => $status, 'contract' => $contract]);
        }

        return view('sign.show', compact('contract', 'token'));
    }

    /**
     * Process the external user's consent and notify the admin.
     */
    public function store(string $token, ConsentRequest $request)
    {
        $contract = $this->signingService->findByToken($token);

        if (! $contract) {
            return view('sign.invalid', ['reason' => 'not_found']);
        }

        $status = $this->signingService->getTokenStatus($contract);

        if ($status !== 'valid') {
            return view('sign.invalid', ['reason' => $status, 'contract' => $contract]);
        }

        $this->signingService->sign($contract, $request->validated(), $request);

        // Non-blocking: errors must not prevent the user from seeing the completion page
        try {
            $this->mailService->sendSignedNotification($contract->fresh()->load('project.company'), $request);
        } catch (\Throwable $e) {
            Log::error('Notification mail failed after signing', [
                'contract_id' => $contract->id,
                'error'       => $e->getMessage(),
            ]);
        }

        return view('sign.complete', [
            'contract' => $contract->fresh(),
            'signedAt' => now(),
        ]);
    }
}
