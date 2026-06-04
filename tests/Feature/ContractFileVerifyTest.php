<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractFile;
use App\Models\User;
use App\Services\ContractFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractFileVerifyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Helper: create a ContractFile backed by a real file on the fake disk.
     */
    private function makeFileWithContent(Contract $contract, string $content): ContractFile
    {
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

    /** Hash verification matches when file content is unchanged. */
    public function test_hash_verification_returns_matched_for_untampered_file(): void
    {
        Storage::fake('contracts');

        $contract = Contract::factory()->create();
        $file     = $this->makeFileWithContent($contract, '%PDF-test-content');

        $this->actingAs($this->user)
            ->post(route('contracts.files.verify-hash', [$contract, $file]))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success', 'このPDFは保存時から変更されていません。');
    }

    /** Hash verification returns mismatched when file content differs from stored hash. */
    public function test_hash_verification_returns_mismatched_for_tampered_file(): void
    {
        Storage::fake('contracts');

        $contract = Contract::factory()->create();
        $file     = $this->makeFileWithContent($contract, '%PDF-original-content');

        // Overwrite the file with different content to simulate tampering
        Storage::disk('contracts')->put($file->file_path, '%PDF-tampered-content');

        $this->actingAs($this->user)
            ->post(route('contracts.files.verify-hash', [$contract, $file]))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('warning', '警告：PDFファイルが保存時から変更されている可能性があります。');
    }

    /** Hash verification returns missing when the physical file does not exist on disk. */
    public function test_hash_verification_returns_missing_when_file_not_on_disk(): void
    {
        Storage::fake('contracts');

        $contract = Contract::factory()->create();

        // Create a DB record without actually putting the file on disk
        $file = ContractFile::create([
            'contract_id' => $contract->id,
            'file_name'   => 'ghost.pdf',
            'file_path'   => 'ghost.pdf',
            'file_type'   => 'pdf',
            'file_hash'   => hash('sha256', 'phantom'),
        ]);

        $this->actingAs($this->user)
            ->post(route('contracts.files.verify-hash', [$contract, $file]))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('warning', '警告：PDFファイルが見つかりません。');
    }

    /** A ContractFile belonging to another contract cannot be verified via the wrong contract route. */
    public function test_cannot_verify_file_belonging_to_another_contract(): void
    {
        Storage::fake('contracts');

        $contractA = Contract::factory()->create();
        $contractB = Contract::factory()->create();
        $fileA     = $this->makeFileWithContent($contractA, '%PDF-contract-a');

        // Attempt to verify fileA using contractB's route
        $this->actingAs($this->user)
            ->post(route('contracts.files.verify-hash', [$contractB, $fileA]))
            ->assertNotFound();
    }

    /** PDF generation stores a file and records an audit log. */
    public function test_pdf_generation_creates_file_and_audit_log(): void
    {
        Storage::fake('contracts');

        $contract = Contract::factory()->create();
        $contract->load('project.company');

        $this->actingAs($this->user)
            ->post(route('contracts.pdf.store', $contract))
            ->assertRedirect(route('contracts.show', $contract))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contract_files', [
            'contract_id' => $contract->id,
            'file_type'   => 'pdf',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'contract_pdf_generated',
            'target_type' => 'contract',
            'target_id'   => $contract->id,
        ]);
    }

    /** getLatestPdf returns null when no files exist. */
    public function test_get_latest_pdf_returns_null_when_no_files(): void
    {
        $contract = Contract::factory()->create();
        $contract->load('files');

        $service = app(ContractFileService::class);

        $this->assertNull($service->getLatestPdf($contract));
    }
}
