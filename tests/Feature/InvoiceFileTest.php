<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceFileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('invoices');
        $this->user = User::factory()->create();
    }

    /** Invoice show page renders the PDF preview link and regenerate button. */
    public function test_invoice_show_has_pdf_generate_button(): void
    {
        $invoice = Invoice::factory()->create();
        $invoice->load('project.company');

        $this->actingAs($this->user)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('PDFプレビュー')
            ->assertSee('PDFを生成');
    }

    /** Posting to the generate endpoint succeeds and redirects. */
    public function test_invoice_pdf_can_be_generated(): void
    {
        $invoice = Invoice::factory()->create();
        $invoice->load('project.company');

        $this->actingAs($this->user)
            ->post(route('invoices.pdf.store', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('success');
    }

    /** An InvoiceFile record is created in the database. */
    public function test_invoice_file_record_is_created(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.pdf.store', $invoice));

        $this->assertDatabaseHas('invoice_files', [
            'invoice_id' => $invoice->id,
            'file_type'  => 'pdf',
        ]);
    }

    /** The PDF file is saved to the invoices storage disk. */
    public function test_pdf_file_is_saved_to_invoices_disk(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.pdf.store', $invoice));

        $file = InvoiceFile::where('invoice_id', $invoice->id)->first();
        Storage::disk('invoices')->assertExists($file->file_path);
    }

    /** The SHA256 hash (64 hex chars) is saved in file_hash. */
    public function test_file_hash_is_saved(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.pdf.store', $invoice));

        $file = InvoiceFile::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($file->file_hash);
        $this->assertSame(64, strlen($file->file_hash));
    }

    /** An AuditLog entry is created with action=invoice_pdf_generated. */
    public function test_audit_log_invoice_pdf_generated_is_created(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.pdf.store', $invoice));

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'invoice_pdf_generated',
            'target_type' => 'invoice',
            'target_id'   => $invoice->id,
        ]);
    }

    /** A stored PDF can be downloaded via the download endpoint. */
    public function test_pdf_can_be_downloaded(): void
    {
        $invoice = Invoice::factory()->create();
        Storage::disk('invoices')->put('test.pdf', '%PDF-test-content');

        $file = InvoiceFile::create([
            'invoice_id' => $invoice->id,
            'file_name'  => 'test.pdf',
            'file_path'  => 'test.pdf',
            'file_type'  => 'pdf',
            'file_hash'  => hash('sha256', '%PDF-test-content'),
        ]);

        $this->actingAs($this->user)
            ->get(route('invoices.files.download', [$invoice, $file]))
            ->assertOk();
    }

    /** A file belonging to another invoice cannot be downloaded via this invoice's URL. */
    public function test_cannot_download_file_from_another_invoice(): void
    {
        $invoiceA = Invoice::factory()->create();
        $invoiceB = Invoice::factory()->create();

        Storage::disk('invoices')->put('file-a.pdf', '%PDF-invoice-a');
        $fileA = InvoiceFile::create([
            'invoice_id' => $invoiceA->id,
            'file_name'  => 'file-a.pdf',
            'file_path'  => 'file-a.pdf',
            'file_type'  => 'pdf',
            'file_hash'  => hash('sha256', '%PDF-invoice-a'),
        ]);

        $this->actingAs($this->user)
            ->get(route('invoices.files.download', [$invoiceB, $fileA]))
            ->assertNotFound();
    }

    /** Multiple PDFs can be generated; the latest is shown correctly. */
    public function test_latest_pdf_is_tracked(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.pdf.store', $invoice));

        $this->actingAs($this->user)
            ->post(route('invoices.pdf.store', $invoice));

        $this->assertSame(2, InvoiceFile::where('invoice_id', $invoice->id)->count());
    }
}
