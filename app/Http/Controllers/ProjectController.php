<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Company;
use App\Models\Project;
use App\Services\ProjectService;

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

    public function show(Project $project)
    {
        $project->load('company', 'contracts');

        $invoices = $project->invoices()->with('payments', 'contract')->get();

        return view('projects.show', compact('project', 'invoices'));
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
