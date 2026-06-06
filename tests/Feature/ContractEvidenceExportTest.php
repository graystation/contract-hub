<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\ContractEvidenceExport;
use App\Models\ContractFile;
use App\Models\User;
use App\Services\ContractEvidenceExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ContractEvidenceExportTest extends TestCase
{
    use RefreshDatabase;

    private ContractEvidenceExportService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ContractEvidenceExportService::class);
        $this->user    = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // Baseline model / relationship tests (Phase 4-A)
    // -------------------------------------------------------------------------

    /** A ContractEvidenceExport record can be saved to the database. */
    public function test_record_can_be_saved_to_database(): void
    {
        $contract = Contract::factory()->create();

        ContractEvidenceExport::create([
            'contract_id'  => $contract->id,
            'file_name'    => 'evidence_CTR-0001_20260101000000.zip',
            'file_path'    => 'evidence_CTR-0001_20260101000000.zip',
            'file_hash'    => null,
            'generated_at' => now(),
        ]);

        $this->assertDatabaseHas('contract_evidence_exports', [
            'contract_id' => $contract->id,
            'file_name'   => 'evidence_CTR-0001_20260101000000.zip',
        ]);
    }

    /** Contract has many evidenceExports and the relationship returns them. */
    public function test_contract_has_many_evidence_exports(): void
    {
        $contract = Contract::factory()->create();

        ContractEvidenceExport::factory()->count(3)->create([
            'contract_id' => $contract->id,
        ]);

        $this->assertSame(3, $contract->evidenceExports()->count());
    }

    /** ContractEvidenceExport belongs to a Contract. */
    public function test_evidence_export_belongs_to_contract(): void
    {
        $contract = Contract::factory()->create();
        $export   = ContractEvidenceExport::factory()->create(['contract_id' => $contract->id]);

        $this->assertInstanceOf(Contract::class, $export->contract);
        $this->assertSame($contract->id, $export->contract->id);
    }

    /** createPlaceholderExport creates a database record with expected fields. */
    public function test_create_placeholder_export_saves_record(): void
    {
        Storage::fake('contract_evidence');

        $contract = Contract::factory()->create();
        $export   = $this->service->createPlaceholderExport($contract);

        $this->assertInstanceOf(ContractEvidenceExport::class, $export);
        $this->assertSame($contract->id, $export->contract_id);
        $this->assertNotEmpty($export->file_name);
        $this->assertNotEmpty($export->file_path);
        $this->assertNotNull($export->generated_at);

        $this->assertDatabaseHas('contract_evidence_exports', [
            'contract_id' => $contract->id,
            'file_name'   => $export->file_name,
        ]);
    }

    /** getExportsForContract returns only exports for that contract. */
    public function test_get_exports_for_contract_returns_all_records(): void
    {
        $contract = Contract::factory()->create();
        ContractEvidenceExport::factory()->count(2)->create(['contract_id' => $contract->id]);

        $other = Contract::factory()->create();
        ContractEvidenceExport::factory()->create(['contract_id' => $other->id]);

        $exports = $this->service->getExportsForContract($contract);

        $this->assertSame(2, $exports->count());
        $exports->each(fn ($e) => $this->assertSame($contract->id, $e->contract_id));
    }

    /** getLatestExport returns the most recently created record. */
    public function test_get_latest_export_returns_newest_record(): void
    {
        $contract = Contract::factory()->create();

        ContractEvidenceExport::factory()->create(['contract_id' => $contract->id]);

        $this->travel(1)->seconds();
        $second = ContractEvidenceExport::factory()->create(['contract_id' => $contract->id]);

        $this->assertSame($second->id, $this->service->getLatestExport($contract)->id);
    }

    /** getLatestExport returns null when no exports exist. */
    public function test_get_latest_export_returns_null_when_none(): void
    {
        $contract = Contract::factory()->create();

        $this->assertNull($this->service->getLatestExport($contract));
    }

    // -------------------------------------------------------------------------
    // generateEvidenceZip tests (Phase 4-B)
    // -------------------------------------------------------------------------

    /** generateEvidenceZip creates a ContractEvidenceExport record. */
    public function test_generate_evidence_zip_creates_export_record(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        $this->makeContractFile($contract);

        $export = $this->service->generateEvidenceZip($contract, $this->fakeRequest());

        $this->assertInstanceOf(ContractEvidenceExport::class, $export);
        $this->assertSame($contract->id, $export->contract_id);
        $this->assertNotNull($export->generated_at);

        $this->assertDatabaseHas('contract_evidence_exports', [
            'contract_id' => $contract->id,
            'file_name'   => $export->file_name,
        ]);
    }

    /** generateEvidenceZip saves the ZIP file to the contract_evidence disk. */
    public function test_generate_evidence_zip_saves_zip_file(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        $this->makeContractFile($contract);

        $export = $this->service->generateEvidenceZip($contract, $this->fakeRequest());

        Storage::disk('contract_evidence')->assertExists($export->file_path);
    }

    /** generateEvidenceZip stores a 64-character SHA256 hash in file_hash. */
    public function test_generate_evidence_zip_stores_sha256_hash(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        $this->makeContractFile($contract);

        $export = $this->service->generateEvidenceZip($contract, $this->fakeRequest());

        $this->assertNotNull($export->file_hash);
        $this->assertSame(64, strlen($export->file_hash));
    }

    /** The generated ZIP contains contract.pdf, audit-log.csv, metadata.json, and hash.txt. */
    public function test_generate_evidence_zip_contains_required_files(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        $this->makeContractFile($contract);

        $export  = $this->service->generateEvidenceZip($contract, $this->fakeRequest());
        $entries = $this->listZipEntries($export->file_path);

        $this->assertContains('contract.pdf',  $entries);
        $this->assertContains('audit-log.csv', $entries);
        $this->assertContains('metadata.json', $entries);
        $this->assertContains('hash.txt',      $entries);
    }

    /** metadata.json contains the contract number, company name, and signer information. */
    public function test_metadata_json_contains_contract_info(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        $this->makeContractFile($contract);
        $contract->load('project.company');

        $export    = $this->service->generateEvidenceZip($contract, $this->fakeRequest());
        $metadata  = $this->readZipEntry($export->file_path, 'metadata.json');
        $data      = json_decode($metadata, true);

        $this->assertSame($contract->contract_number,            $data['contract_number']);
        $this->assertSame($contract->project->company->company_name, $data['company_name']);
        $this->assertSame($contract->signer_name,                $data['signer_name']);
        $this->assertSame($contract->signer_email,               $data['signer_email']);
        $this->assertArrayHasKey('evidence_generated_at',        $data);
        $this->assertArrayHasKey('app_url',                      $data);
    }

    /** audit-log.csv contains rows for audit logs related to the contract. */
    public function test_audit_log_csv_contains_related_logs(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        $this->makeContractFile($contract);

        AuditLog::create([
            'user_id'     => null,
            'action'      => 'contract_sent',
            'target_type' => 'contract',
            'target_id'   => $contract->id,
            'ip_address'  => '10.0.0.1',
            'user_agent'  => 'TestAgent',
        ]);

        $export = $this->service->generateEvidenceZip($contract, $this->fakeRequest());
        $csv    = $this->readZipEntry($export->file_path, 'audit-log.csv');

        $this->assertStringContainsString('contract_sent', $csv);
        $this->assertStringContainsString('10.0.0.1',      $csv);
    }

    /** When no contract PDF exists, generateEvidenceZip auto-generates one. */
    public function test_generate_evidence_zip_auto_generates_pdf_if_none_exists(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        // No ContractFile created — service must auto-generate

        $this->assertSame(0, ContractFile::where('contract_id', $contract->id)->count());

        $export = $this->service->generateEvidenceZip($contract, $this->fakeRequest());

        $this->assertInstanceOf(ContractEvidenceExport::class, $export);
        $this->assertSame(1, ContractFile::where('contract_id', $contract->id)->count());
    }

    /** generateEvidenceZip records an audit log with action=contract_evidence_export_generated. */
    public function test_audit_log_is_created(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeSignedContract();
        $this->makeContractFile($contract);

        $this->service->generateEvidenceZip($contract, $this->fakeRequest());

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'contract_evidence_export_generated',
            'target_type' => 'contract',
            'target_id'   => $contract->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeSignedContract(): Contract
    {
        return Contract::factory()->create([
            'status'           => 'signed',
            'signed_at'        => now()->subDays(3),
            'signer_name'      => 'Test Signer',
            'signer_email'     => 'signer@example.com',
            'signer_ip_address' => '192.168.1.1',
            'signer_user_agent' => 'Mozilla/5.0',
        ]);
    }

    private function makeContractFile(Contract $contract): ContractFile
    {
        $content  = '%PDF-fake-content';
        $fileName = $contract->contract_number . '_test.pdf';

        Storage::disk('contracts')->put($fileName, $content);

        return ContractFile::create([
            'contract_id' => $contract->id,
            'file_name'   => $fileName,
            'file_path'   => $fileName,
            'file_type'   => 'pdf',
            'file_hash'   => hash('sha256', $content),
        ]);
    }

    private function fakeRequest(): Request
    {
        return Request::create('/test', 'POST', [], [], [], [
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_USER_AGENT' => 'PHPUnit Test',
        ]);
    }

    /** List all file names inside the ZIP stored on the contract_evidence disk. */
    private function listZipEntries(string $filePath): array
    {
        $zipPath = Storage::disk('contract_evidence')->path($filePath);

        $zip     = new ZipArchive();
        $zip->open($zipPath);
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();

        return $entries;
    }

    /** Read a single file's content from the ZIP stored on the contract_evidence disk. */
    private function readZipEntry(string $filePath, string $entryName): string
    {
        $zipPath = Storage::disk('contract_evidence')->path($filePath);

        $zip     = new ZipArchive();
        $zip->open($zipPath);
        $content = $zip->getFromName($entryName);
        $zip->close();

        return $content;
    }
}
