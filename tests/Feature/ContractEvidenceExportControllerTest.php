<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractEvidenceExport;
use App\Models\ContractFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractEvidenceExportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** The contract show page renders the evidence ZIP generate button. */
    public function test_show_page_has_evidence_export_button(): void
    {
        $contract = Contract::factory()->create(['status' => 'signed']);
        $contract->load('project.company');

        $this->actingAs($this->user)
            ->get(route('contracts.show', $contract))
            ->assertOk()
            ->assertSee('証跡ZIPを生成');
    }

    /** Posting to the store endpoint generates a ZIP and redirects with success. */
    public function test_can_generate_evidence_zip(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeContractWithFile();

        $this->actingAs($this->user)
            ->post(route('contracts.evidence-exports.store', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success');
    }

    /** Generating a ZIP creates a new ContractEvidenceExport record. */
    public function test_generating_evidence_zip_creates_record(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeContractWithFile();

        $before = ContractEvidenceExport::where('contract_id', $contract->id)->count();

        $this->actingAs($this->user)
            ->post(route('contracts.evidence-exports.store', $contract));

        $this->assertSame($before + 1, ContractEvidenceExport::where('contract_id', $contract->id)->count());
    }

    /** Generating a ZIP shows a success flash message. */
    public function test_generating_evidence_zip_shows_success_flash(): void
    {
        Storage::fake('contracts');
        Storage::fake('contract_evidence');

        $contract = $this->makeContractWithFile();

        $this->actingAs($this->user)
            ->post(route('contracts.evidence-exports.store', $contract))
            ->assertSessionHas('success', '証跡ZIPを生成しました。');
    }

    /** The contract show page lists existing evidence exports. */
    public function test_show_page_lists_evidence_exports(): void
    {
        Storage::fake('contract_evidence');

        $contract = Contract::factory()->create();
        $export   = ContractEvidenceExport::factory()->create([
            'contract_id' => $contract->id,
            'file_name'   => 'CTR-TEST-0001_evidence_20260606120000.zip',
        ]);

        $this->actingAs($this->user)
            ->get(route('contracts.show', $contract))
            ->assertOk()
            ->assertSee('CTR-TEST-0001_evidence_20260606120000.zip');
    }

    /** A stored evidence ZIP can be downloaded. */
    public function test_can_download_evidence_zip(): void
    {
        Storage::fake('contract_evidence');

        $contract = Contract::factory()->create();
        $fileName = 'CTR-TEST-0001_evidence_20260606120000.zip';

        Storage::disk('contract_evidence')->put($fileName, 'PK-fake-zip-content');

        $export = ContractEvidenceExport::create([
            'contract_id'  => $contract->id,
            'file_name'    => $fileName,
            'file_path'    => $fileName,
            'file_hash'    => hash('sha256', 'PK-fake-zip-content'),
            'generated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('contracts.evidence-exports.download', [$contract, $export]))
            ->assertOk();
    }

    /** An evidence export belonging to another contract cannot be downloaded. */
    public function test_cannot_download_evidence_export_from_another_contract(): void
    {
        Storage::fake('contract_evidence');

        $contractA = Contract::factory()->create();
        $contractB = Contract::factory()->create();

        Storage::disk('contract_evidence')->put('file-a.zip', 'PK-fake-a');
        $exportA = ContractEvidenceExport::create([
            'contract_id'  => $contractA->id,
            'file_name'    => 'file-a.zip',
            'file_path'    => 'file-a.zip',
            'file_hash'    => hash('sha256', 'PK-fake-a'),
            'generated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('contracts.evidence-exports.download', [$contractB, $exportA]))
            ->assertNotFound();
    }

    /** Downloading a ZIP whose file has been removed from disk returns 404. */
    public function test_download_missing_file_returns_404(): void
    {
        Storage::fake('contract_evidence');

        $contract = Contract::factory()->create();
        $export   = ContractEvidenceExport::create([
            'contract_id'  => $contract->id,
            'file_name'    => 'ghost.zip',
            'file_path'    => 'ghost.zip',
            'file_hash'    => hash('sha256', 'phantom'),
            'generated_at' => now(),
        ]);

        // File intentionally not placed on disk

        $this->actingAs($this->user)
            ->get(route('contracts.evidence-exports.download', [$contract, $export]))
            ->assertNotFound();
    }

    /** Unauthenticated users cannot generate evidence exports. */
    public function test_guest_cannot_generate_evidence_zip(): void
    {
        $contract = Contract::factory()->create();

        $this->post(route('contracts.evidence-exports.store', $contract))
            ->assertRedirect(route('login'));
    }

    /** Unauthenticated users cannot download evidence exports. */
    public function test_guest_cannot_download_evidence_zip(): void
    {
        $contract = Contract::factory()->create();
        $export   = ContractEvidenceExport::factory()->create(['contract_id' => $contract->id]);

        $this->get(route('contracts.evidence-exports.download', [$contract, $export]))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function makeContractWithFile(): Contract
    {
        $contract = Contract::factory()->create([
            'status'            => 'signed',
            'signed_at'         => now()->subDays(1),
            'signer_name'       => 'Test Signer',
            'signer_email'      => 'signer@example.com',
            'signer_ip_address' => '192.168.1.1',
            'signer_user_agent' => 'Mozilla/5.0',
        ]);
        $contract->load('project.company');

        $content  = '%PDF-fake-content';
        $fileName = $contract->contract_number . '_test.pdf';

        Storage::disk('contracts')->put($fileName, $content);

        ContractFile::create([
            'contract_id' => $contract->id,
            'file_name'   => $fileName,
            'file_path'   => $fileName,
            'file_type'   => 'pdf',
            'file_hash'   => hash('sha256', $content),
        ]);

        return $contract;
    }
}
