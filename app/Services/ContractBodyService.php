<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;

class ContractBodyService
{
    /**
     * Available template variables grouped by category (used for the editor UI).
     */
    public const VARIABLE_GROUPS = [
        '契約情報' => [
            '{contract_number}' => '契約番号',
            '{contract_type}'   => '契約種別',
            '{today}'           => '今日の日付',
        ],
        '案件情報' => [
            '{project_title}' => '案件名',
            '{start_date}'    => '案件開始日',
            '{end_date}'      => '案件終了日',
        ],
        '顧客情報' => [
            '{company_name}'  => '会社名',
            '{contact_name}'  => '担当者名',
        ],
        '運営者情報（甲）' => [
            '{operator_company}' => '会社名',
            '{operator_name}'    => '氏名',
            '{operator_address}' => '住所',
        ],
        '署名欄（同意後に反映）（乙）' => [
            '{signer_name}'    => '同意者氏名',
            '{signer_address}' => '同意者住所',
            '{signed_date}'    => '同意日',
        ],
    ];

    /** Flat map of all variables — used by resolve() and for validation. */
    public const VARIABLES = [
        '{contract_number}'  => '契約番号',
        '{contract_type}'    => '契約種別',
        '{today}'            => '今日の日付',
        '{project_title}'    => '案件名',
        '{start_date}'       => '案件開始日',
        '{end_date}'         => '案件終了日',
        '{company_name}'     => '会社名（乙）',
        '{contact_name}'     => '担当者名（乙）',
        '{operator_company}' => '運営者会社名（甲）',
        '{operator_name}'    => '運営者氏名（甲）',
        '{operator_address}' => '運営者住所（甲）',
        '{signer_name}'      => '同意者氏名（署名欄）',
        '{signer_address}'   => '同意者住所（署名欄）',
        '{signed_date}'      => '同意日（署名欄）',
    ];

    /**
     * Resolve template variables in the body with actual contract data.
     * Called when applying a template or generating a PDF.
     */
    public function resolve(string $body, Contract $contract): string
    {
        $contract->loadMissing('project.company');

        $map = [
            '{company_name}'     => $contract->project->company->company_name,
            '{contact_name}'     => $contract->project->company->contact_name ?? '',
            '{project_title}'    => $contract->project->title,
            '{contract_number}'  => $contract->contract_number,
            '{contract_type}'    => $contract->contract_type,
            '{start_date}'       => $contract->project->started_at?->format('Y年m月d日') ?? '',
            '{end_date}'         => $contract->project->ended_at?->format('Y年m月d日') ?? '',
            '{today}'            => now()->format('Y年m月d日'),
            '{operator_company}' => config('contract.operator_company') ?? '',
            '{operator_name}'    => config('contract.operator_name') ?? '',
            '{operator_address}' => config('contract.operator_address') ?? '',
            '{signer_name}'      => $contract->signer_name ?? '（同意後に記載）',
            '{signer_address}'   => $contract->signer_address ?? '（同意後に記載）',
            '{signed_date}'      => $contract->signed_at?->format('Y年m月d日') ?? '（同意後に記載）',
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
