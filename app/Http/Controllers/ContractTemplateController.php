<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractTemplateRequest;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Project;
use App\Services\ContractBodyService;

class ContractTemplateController extends Controller
{
    public function __construct(private ContractBodyService $bodyService) {}

    public function index()
    {
        return view('contract-templates.index', [
            'templates' => ContractTemplate::orderBy('title')->get(),
        ]);
    }

    public function create()
    {
        return view('contract-templates.create', [
            'template'       => new ContractTemplate(),
            'variableGroups' => ContractBodyService::VARIABLE_GROUPS,
        ]);
    }

    public function store(ContractTemplateRequest $request)
    {
        ContractTemplate::create($request->validated());

        return redirect()->route('contract-templates.index')
            ->with('success', 'テンプレートを作成しました。');
    }

    public function edit(ContractTemplate $contractTemplate)
    {
        return view('contract-templates.edit', [
            'template'       => $contractTemplate,
            'variableGroups' => ContractBodyService::VARIABLE_GROUPS,
        ]);
    }

    public function update(ContractTemplateRequest $request, ContractTemplate $contractTemplate)
    {
        $contractTemplate->update($request->validated());

        return redirect()->route('contract-templates.index')
            ->with('success', 'テンプレートを更新しました。');
    }

    public function destroy(ContractTemplate $contractTemplate)
    {
        $contractTemplate->delete();

        return redirect()->route('contract-templates.index')
            ->with('success', 'テンプレートを削除しました。');
    }

    /**
     * Return the template body with variables resolved for the given project.
     * Called via AJAX from the contract form.
     */
    public function preview(ContractTemplate $contractTemplate)
    {
        $projectId      = request('project_id');
        $contractNumber = request('contract_number', '');
        $contractType   = request('contract_type', '');

        if (! $projectId) {
            return response()->json(['body' => $contractTemplate->body]);
        }

        $project = Project::with('company')->find($projectId);
        if (! $project) {
            return response()->json(['body' => $contractTemplate->body]);
        }

        // Build a temporary Contract to resolve variables
        $stub = new Contract([
            'project_id'      => $project->id,
            'contract_number' => $contractNumber ?: '（採番前）',
            'contract_type'   => $contractType,
        ]);
        $stub->setRelation('project', $project);

        $resolved = $this->bodyService->resolve($contractTemplate->body, $stub);

        return response()->json(['body' => $resolved]);
    }
}
