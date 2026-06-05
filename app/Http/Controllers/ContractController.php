<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractRequest;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Project;
use App\Services\ContractFileService;
use App\Services\ContractService;

class ContractController extends Controller
{
    public function __construct(
        private ContractService $service,
        private ContractFileService $fileService,
    ) {}

    public function index()
    {
        return view('contracts.index', [
            'contracts' => $this->service->paginate(),
        ]);
    }

    public function create()
    {
        return view('contracts.create', [
            'projects'       => Project::with('company')->orderByDesc('created_at')->get(),
            'statuses'       => Contract::STATUSES,
            'contractNumber' => $this->service->generateContractNumber(),
            'templates'      => ContractTemplate::orderBy('title')->get(),
        ]);
    }

    public function store(ContractRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('contracts.index')->with('success', '契約を登録しました。');
    }

    public function show(Contract $contract)
    {
        $contract->load('project.company', 'files');

        $latestPdf  = $this->fileService->getLatestPdf($contract);
        $auditLogs  = $this->fileService->getRelatedAuditLogs($contract);

        return view('contracts.show', compact('contract', 'latestPdf', 'auditLogs'));
    }

    public function edit(Contract $contract)
    {
        return view('contracts.edit', [
            'contract'  => $contract,
            'projects'  => Project::with('company')->orderByDesc('created_at')->get(),
            'statuses'  => Contract::STATUSES,
            'templates' => ContractTemplate::orderBy('title')->get(),
        ]);
    }

    public function update(ContractRequest $request, Contract $contract)
    {
        $this->service->update($contract, $request->validated());

        return redirect()->route('contracts.show', $contract)->with('success', '契約を更新しました。');
    }

    public function destroy(Contract $contract)
    {
        $this->service->delete($contract);

        return redirect()->route('contracts.index')->with('success', '契約を削除しました。');
    }
}
