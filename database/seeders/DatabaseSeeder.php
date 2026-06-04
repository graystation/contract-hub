<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'              => '管理者',
            'email'             => 'koizumi.office.llc.2022@gmail.com',
            'password'          => Hash::make('Hub#2026!Kz@'),
            'email_verified_at' => now(),
        ]);

        // Sample companies
        $companies = [
            Company::create([
                'company_name' => '株式会社サンプルコーポレーション',
                'contact_name' => '田中 太郎',
                'email'        => 'tanaka@sample-corp.co.jp',
                'phone'        => '03-1234-5678',
                'address'      => '東京都渋谷区神宮前1-1-1',
                'notes'        => 'Webサイト広告掲載の主要顧客',
            ]),
            Company::create([
                'company_name' => '有限会社テックパートナーズ',
                'contact_name' => '鈴木 花子',
                'email'        => 'suzuki@tech-partners.jp',
                'phone'        => '06-9876-5432',
                'address'      => '大阪府大阪市北区梅田2-2-2',
                'notes'        => 'ITコンサルティング契約中',
            ]),
            Company::create([
                'company_name' => '合同会社AIソリューションズ',
                'contact_name' => '山田 次郎',
                'email'        => 'yamada@ai-solutions.jp',
                'phone'        => '052-111-2222',
                'address'      => '愛知県名古屋市中区栄3-3-3',
                'notes'        => 'AI導入支援プロジェクト中',
            ]),
        ];

        // Sample projects
        $projects = [
            Project::create([
                'company_id'  => $companies[0]->id,
                'title'       => 'コーポレートサイト広告掲載2026',
                'type'        => 'advertisement',
                'description' => 'コーポレートサイトのバナー広告掲載契約。月次掲載料金方式。',
                'status'      => 'active',
                'started_at'  => '2026-01-01',
                'ended_at'    => '2026-12-31',
            ]),
            Project::create([
                'company_id'  => $companies[0]->id,
                'title'       => 'ECサイト広告掲載Q2',
                'type'        => 'advertisement',
                'description' => 'ECサイトのスポンサー広告枠。第2四半期限定掲載。',
                'status'      => 'draft',
                'started_at'  => '2026-04-01',
                'ended_at'    => '2026-06-30',
            ]),
            Project::create([
                'company_id'  => $companies[1]->id,
                'title'       => 'DX推進コンサルティング',
                'type'        => 'consulting',
                'description' => '社内業務のデジタル化支援。月次ミーティング込み。',
                'status'      => 'active',
                'started_at'  => '2026-02-01',
                'ended_at'    => '2026-07-31',
            ]),
            Project::create([
                'company_id'  => $companies[2]->id,
                'title'       => 'AI導入支援フェーズ1',
                'type'        => 'consulting',
                'description' => '生成AIを活用した業務効率化の導入支援。要件定義から運用まで。',
                'status'      => 'active',
                'started_at'  => '2026-03-01',
                'ended_at'    => '2026-08-31',
            ]),
            Project::create([
                'company_id'  => $companies[1]->id,
                'title'       => 'セキュリティ監査2025',
                'type'        => 'other',
                'description' => '情報セキュリティの年次監査対応支援。完了済み。',
                'status'      => 'completed',
                'started_at'  => '2025-10-01',
                'ended_at'    => '2025-12-31',
            ]),
        ];

        // Sample contracts
        $contracts = [
            Contract::create([
                'project_id'      => $projects[0]->id,
                'contract_number' => 'CTR-2026-0001',
                'contract_type'   => '広告掲載契約',
                'status'          => 'signed',
                'signed_at'       => '2025-12-20',
                'notes'           => '年間契約。自動更新条項あり。',
            ]),
            Contract::create([
                'project_id'      => $projects[1]->id,
                'contract_number' => 'CTR-2026-0002',
                'contract_type'   => '広告掲載契約',
                'status'          => 'draft',
                'signed_at'       => null,
                'notes'           => '条件交渉中。',
            ]),
            Contract::create([
                'project_id'      => $projects[2]->id,
                'contract_number' => 'CTR-2026-0003',
                'contract_type'   => '業務委託契約',
                'status'          => 'signed',
                'signed_at'       => '2026-01-31',
                'notes'           => '月次報告義務あり。',
            ]),
            Contract::create([
                'project_id'      => $projects[3]->id,
                'contract_number' => 'CTR-2026-0004',
                'contract_type'   => 'AI導入支援業務委託契約',
                'status'          => 'sent',
                'signed_at'       => null,
                'notes'           => '先方確認待ち。',
            ]),
            Contract::create([
                'project_id'      => $projects[4]->id,
                'contract_number' => 'CTR-2025-0001',
                'contract_type'   => 'セキュリティ監査委託契約',
                'status'          => 'signed',
                'signed_at'       => '2025-09-30',
                'notes'           => '完了済み。',
            ]),
        ];
    }
}
