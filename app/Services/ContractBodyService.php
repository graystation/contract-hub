<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;

class ContractBodyService
{
    /**
     * Available template variables and their descriptions.
     */
    public const VARIABLES = [
        '{company_name}'    => '会社名',
        '{contact_name}'    => '担当者名',
        '{project_title}'   => '案件名',
        '{contract_number}' => '契約番号',
        '{contract_type}'   => '契約種別',
        '{start_date}'      => '案件開始日',
        '{end_date}'        => '案件終了日',
        '{today}'           => '今日の日付',
    ];

    /**
     * Resolve template variables in the body with actual contract data.
     * Called when applying a template or generating a PDF.
     */
    public function resolve(string $body, Contract $contract): string
    {
        $contract->loadMissing('project.company');

        $map = [
            '{company_name}'    => $contract->project->company->company_name,
            '{contact_name}'    => $contract->project->company->contact_name ?? '',
            '{project_title}'   => $contract->project->title,
            '{contract_number}' => $contract->contract_number,
            '{contract_type}'   => $contract->contract_type,
            '{start_date}'      => $contract->project->started_at?->format('Y年m月d日') ?? '',
            '{end_date}'        => $contract->project->ended_at?->format('Y年m月d日') ?? '',
            '{today}'           => now()->format('Y年m月d日'),
        ];

        return str_replace(array_keys($map), array_values($map), $body);
    }

    /**
     * Apply a template to a stub contract for preview purposes.
     * The contract may not yet be persisted.
     */
    public function applyTemplate(ContractTemplate $template, Contract $contract): string
    {
        return $this->resolve($template->body, $contract);
    }
}
