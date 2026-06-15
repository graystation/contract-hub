<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsentRequest;
use App\Services\ContractFileService;
use App\Services\ContractMailService;
use App\Services\ContractSigningService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConsentController extends Controller
{
    public function __construct(
        private ContractSigningService $signingService,
        private ContractMailService $mailService,
        private ContractFileService $fileService,
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

    /**
     * Download the signed contract PDF using the sign token.
     * Only available once the contract has been signed.
     */
    public function downloadPdf(string $token)
    {
        $contract = $this->signingService->findByToken($token);

        abort_if(! $contract || $contract->status !== 'signed', 404);

        $contractFile = $this->fileService->getLatestPdf($contract);

        abort_unless(
            $contractFile && Storage::disk('contracts')->exists($contractFile->file_path),
            404,
            'ファイルが見つかりません。',
        );

        return Storage::disk('contracts')->download(
            $contractFile->file_path,
            $contractFile->file_name,
        );
    }
}
