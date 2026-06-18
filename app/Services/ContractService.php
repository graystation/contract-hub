<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Pagination\LengthAwarePaginator;

class ContractService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Contract::with('project.company')->orderByDesc('created_at')->paginate($perPage);
    }

    public function generateContractNumber(): string
    {
        $year = now()->format('Y');
        $latest = Contract::whereYear('created_at', $year)->count() + 1;

        return sprintf('CTR-%s-%04d', $year, $latest);
    }

    public function create(array $data): Contract
    {
        return Contract::create($data);
    }

    public function update(Contract $contract, array $data): Contract
    {
        $contract->update($data);

        return $contract;
    }

    public function duplicate(Contract $contract): Contract
    {
        return Contract::create([
            'project_id'      => $contract->project_id,
            'template_id'     => $contract->template_id,
            'contract_number' => $this->generateContractNumber(),
            'contract_type'   => $contract->contract_type,
            'notes'           => $contract->notes,
            'body'            => $contract->body,
            'status'          => 'draft',
        ]);
    }

    public function delete(Contract $contract): void
    {
        $contract->delete();
    }
}
