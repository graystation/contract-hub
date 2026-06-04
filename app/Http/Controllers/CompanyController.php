<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;

class CompanyController extends Controller
{
    public function __construct(private CompanyService $service) {}

    public function index()
    {
        return view('companies.index', [
            'companies' => $this->service->paginate(),
        ]);
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(CompanyRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('companies.index')->with('success', '顧客を登録しました。');
    }

    public function show(Company $company)
    {
        $company->load([
            'projects' => fn ($q) => $q->withCount('contracts')->orderByDesc('created_at'),
        ]);

        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $this->service->update($company, $request->validated());

        return redirect()->route('companies.show', $company)->with('success', '顧客情報を更新しました。');
    }

    public function destroy(Company $company)
    {
        $this->service->delete($company);

        return redirect()->route('companies.index')->with('success', '顧客を削除しました。');
    }
}
