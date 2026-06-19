<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Company;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $service) {}

    public function index()
    {
        return view('projects.index', [
            'projects' => $this->service->paginate(),
        ]);
    }

    public function create()
    {
        return view('projects.create', [
            'companies' => Company::orderBy('company_name')->get(),
            'statuses'  => Project::STATUSES,
            'types'     => Project::TYPES,
        ]);
    }

    public function store(ProjectRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('projects.index')->with('success', '案件を登録しました。');
    }

    public function show(Project $project, Request $request)
    {
        // Contract sort
        $contractSort = $request->query('contract_sort', 'created_at');
        $contractDir  = $request->query('contract_dir', 'desc');
        $contractSort = in_array($contractSort, ['contract_number', 'status', 'signed_at', 'created_at']) ? $contractSort : 'created_at';
        $contractDir  = $contractDir === 'asc' ? 'asc' : 'desc';

        $contracts = $project->contracts()->orderBy($contractSort, $contractDir)->get();

        // Invoice sort
        $invoiceSort = $request->query('invoice_sort', 'created_at');
        $invoiceDir  = $request->query('invoice_dir', 'desc');
        $invoiceSort = in_array($invoiceSort, ['invoice_number', 'contract_id', 'total_amount', 'status', 'issued_at', 'due_date', 'created_at']) ? $invoiceSort : 'created_at';
        $invoiceDir  = $invoiceDir === 'asc' ? 'asc' : 'desc';

        $invoices = $project->invoices()->with('payments', 'contract')->orderBy($invoiceSort, $invoiceDir)->get();

        $project->load('company');

        return view('projects.show', compact('project', 'contracts', 'invoices', 'contractSort', 'contractDir', 'invoiceSort', 'invoiceDir'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', [
            'project'   => $project,
            'companies' => Company::orderBy('company_name')->get(),
            'statuses'  => Project::STATUSES,
            'types'     => Project::TYPES,
        ]);
    }

    public function update(ProjectRequest $request, Project $project)
    {
        $this->service->update($project, $request->validated());

        return redirect()->route('projects.show', $project)->with('success', '案件を更新しました。');
    }

    public function destroy(Project $project)
    {
        $this->service->delete($project);

        return redirect()->route('projects.index')->with('success', '案件を削除しました。');
    }
}
