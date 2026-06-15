<?php

namespace Tests\Feature;

use App\Mail\ContractSignedNotificationMail;
use App\Mail\InvoiceSendMail;
use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractEvidenceExport;
use App\Models\ContractFile;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * End-to-end flow test: company → project → contract → consent → evidence ZIP
 *                        → invoice → payment → paid status.
 */
class FullBusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('contracts');
        Storage::fake('contract_evidence');
        Storage::fake('invoices');
        Mail::fake();
        $this->admin = User::factory()->create();
    }

    /**
     * Walk the complete business lifecycle from customer creation through full payment.
     * Each assertion includes a descriptive failure message for fast diagnosis.
     */
    public function test_full_business_flow(): void
    {
        // ===========================================================
        // Step 1: 顧客作成
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('companies.store'), [
                'company_name' => 'E2E株式会社',
                'contact_name' => '担当 太郎',
                'email'        => 'e2e@example.com',
                'phone'        => '03-0000-0001',
                'address'      => '東京都渋谷区1-1-1',
            ])
            ->assertRedirect();

        $company = Company::where('company_name', 'E2E株式会社')->first();
        $this->assertNotNull($company, '顧客が作成されていない');
        $this->assertSame('e2e@example.com', $company->email);

        // ===========================================================
        // Step 2: 案件作成
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('projects.store'), [
                'company_id'  => $company->id,
                'title'       => 'E2Eテスト案件',
                'type'        => 'consulting',
                'description' => 'フルフロー確認用案件',
                'status'      => 'active',
                'started_at'  => now()->format('Y-m-d'),
            ])
            ->assertRedirect();

        $project = Project::where('title', 'E2Eテスト案件')->first();
        $this->assertNotNull($project, '案件が作成されていない');
        $this->assertSame($company->id, $project->company_id);

        // ===========================================================
        // Step 3: 契約作成
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('contracts.store'), [
                'project_id'      => $project->id,
                'contract_number' => 'CTR-E2E-0001',
                'contract_type'   => '業務委託契約',
                'status'          => 'draft',
            ])
            ->assertRedirect();

        $contract = Contract::where('contract_number', 'CTR-E2E-0001')->first();
        $this->assertNotNull($contract, '契約が作成されていない');
        $this->assertSame('draft', $contract->status);

        // ===========================================================
        // Step 4: 契約PDF生成
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('contracts.pdf.store', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success');

        $this->assertSame(
            1,
            ContractFile::where('contract_id', $contract->id)->count(),
            '契約PDF生成後のContractFileが1件でない',
        );

        // ===========================================================
        // Step 5: 同意URL発行
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('contracts.sign-url.generate', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success');

        $contract->refresh();
        $this->assertNotNull($contract->sign_token, '同意トークンが発行されていない');
        $this->assertSame('sent', $contract->status, '同意URL発行後のステータスがsentでない');

        // ===========================================================
        // Step 6: 外部同意（ログイン不要）
        // ===========================================================
        $this->post(route('sign.contracts.store', $contract->sign_token), [
            'signer_name'               => '外部 同意者',
            'signer_email'              => 'signer@e2e-test.com',
            'signer_email_confirmation' => 'signer@e2e-test.com',
            'signer_address'            => '東京都千代田区〇〇1-2-3',
            'agreed'                    => '1',
        ])->assertOk(); // shows sign.complete view

        Mail::assertSent(ContractSignedNotificationMail::class);

        // ===========================================================
        // Step 7: 同意後の状態確認
        // ===========================================================
        $contract->refresh();
        $this->assertSame('signed', $contract->status, '同意後のステータスがsignedでない');
        $this->assertSame('外部 同意者', $contract->signer_name);
        $this->assertSame('signer@e2e-test.com', $contract->signer_email);
        $this->assertNotNull($contract->signed_at);

        // sign() auto-generates a second PDF with signer details
        $this->assertSame(
            2,
            ContractFile::where('contract_id', $contract->id)->count(),
            '同意後にPDFが再生成されていない（合計2件でない）',
        );

        // ===========================================================
        // Step 8: 証跡ZIP生成
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('contracts.evidence-exports.store', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success', '証跡ZIPを生成しました。');

        $export = ContractEvidenceExport::where('contract_id', $contract->id)->latest()->first();
        $this->assertNotNull($export, '証跡エクスポートレコードが作成されていない');
        $this->assertSame(64, strlen($export->file_hash ?? ''), 'file_hashが64文字でない');
        Storage::disk('contract_evidence')->assertExists($export->file_path);

        // ===========================================================
        // Step 9: ZIP内ファイル確認
        // ===========================================================
        $zipPath = Storage::disk('contract_evidence')->path($export->file_path);
        $zip     = new ZipArchive();
        $zip->open($zipPath);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $this->assertContains('contract.pdf',  $entries, 'ZIPにcontract.pdfがない');
        $this->assertContains('audit-log.csv', $entries, 'ZIPにaudit-log.csvがない');
        $this->assertContains('metadata.json', $entries, 'ZIPにmetadata.jsonがない');
        $this->assertContains('hash.txt',      $entries, 'ZIPにhash.txtがない');

        // ===========================================================
        // Step 10: 請求作成
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('invoices.store'), [
                'project_id' => $project->id,
                'title'      => 'E2Eテスト請求書',
                'amount'     => 100000,
                'status'     => 'issued',
                'issued_at'  => now()->format('Y-m-d'),
                'due_date'   => now()->addDays(30)->format('Y-m-d'),
            ])
            ->assertRedirect();

        $invoice = Invoice::where('title', 'E2Eテスト請求書')->first();
        $this->assertNotNull($invoice, '請求書が作成されていない');
        $this->assertSame(110000, $invoice->total_amount, '消費税込合計が110,000でない');
        $this->assertSame(10000,  $invoice->tax_amount,   '消費税が10,000でない');

        // ===========================================================
        // Step 11: 請求書PDF生成
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('invoices.pdf.store', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('invoice_files', [
            'invoice_id' => $invoice->id,
            'file_type'  => 'pdf',
        ]);

        // ===========================================================
        // Step 12: 請求書メール送信
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('invoices.mail.send', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('success');

        Mail::assertSent(InvoiceSendMail::class, function ($mail) use ($company) {
            return $mail->hasTo($company->email);
        });

        // ===========================================================
        // Step 13: 入金登録（全額）
        // ===========================================================
        $this->actingAs($this->admin)
            ->post(route('invoices.payments.store', $invoice), [
                'amount'  => 110000,
                'paid_at' => now()->format('Y-m-d'),
                'method'  => 'bank_transfer',
            ])
            ->assertRedirect();

        // ===========================================================
        // Step 14: invoice status が paid になる
        // ===========================================================
        $invoice->refresh();
        $this->assertSame('paid', $invoice->status, '全額入金後のステータスがpaidでない');
    }
}
