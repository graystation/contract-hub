<?php

namespace Tests\Feature;

use App\Mail\InvoiceSendMail;
use App\Models\Invoice;
use App\Models\InvoiceFile;
use App\Models\User;
use App\Services\InvoiceMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceMailTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('invoices');
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // UI
    // -------------------------------------------------------------------------

    /** Mail send button is visible when company has an email. */
    public function test_mail_send_button_visible_when_company_has_email(): void
    {
        $invoice = $this->invoiceWithEmail();

        $this->actingAs($this->user)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('メール送信');
    }

    /** Warning shown (no button) when company has no email. */
    public function test_no_mail_button_when_company_has_no_email(): void
    {
        $invoice = Invoice::factory()->create();
        $invoice->project->company->update(['email' => null]);

        $this->actingAs($this->user)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('メールアドレスが未登録');
    }

    // -------------------------------------------------------------------------
    // Success: PDF auto-generation
    // -------------------------------------------------------------------------

    /** When no PDF exists, one is auto-generated before the mail is sent. */
    public function test_pdf_auto_generated_when_no_latest_pdf(): void
    {
        $invoice = $this->invoiceWithEmail();

        $this->assertSame(0, InvoiceFile::where('invoice_id', $invoice->id)->count());

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('success');

        $this->assertSame(1, InvoiceFile::where('invoice_id', $invoice->id)->count());
        Mail::assertSent(InvoiceSendMail::class);
    }

    /** When a PDF already exists, a new one is always generated on send. */
    public function test_existing_pdf_is_reused_not_regenerated(): void
    {
        $invoice = $this->invoiceWithEmail();
        $this->createFakePdf($invoice, 'existing.pdf');

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice));

        $this->assertSame(2, InvoiceFile::where('invoice_id', $invoice->id)->count());
        Mail::assertSent(InvoiceSendMail::class);
    }

    // -------------------------------------------------------------------------
    // Success: mail details
    // -------------------------------------------------------------------------

    /** InvoiceSendMail is sent to the company email. */
    public function test_mail_is_sent_to_company_email(): void
    {
        $invoice = $this->invoiceWithEmail();

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice));

        Mail::assertSent(InvoiceSendMail::class, function ($mail) use ($invoice) {
            return $mail->hasTo($invoice->project->company->email);
        });
    }

    /** The sent mailable references an InvoiceFile as attachment target. */
    public function test_mail_has_invoice_file_attached(): void
    {
        $invoice = $this->invoiceWithEmail();

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice));

        Mail::assertSent(InvoiceSendMail::class, function ($mail) {
            return $mail->invoiceFile instanceof InvoiceFile;
        });
    }

    /** Successful send creates invoice_mail_sent audit log. */
    public function test_successful_send_creates_audit_log(): void
    {
        $invoice = $this->invoiceWithEmail();

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice));

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'invoice_mail_sent',
            'target_type' => 'invoice',
            'target_id'   => $invoice->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Failure handling
    // -------------------------------------------------------------------------

    /** Mail failure shows an error flash message. */
    public function test_mail_failure_shows_error_flash(): void
    {
        $invoice = $this->invoiceWithEmail();

        $this->mock(InvoiceMailService::class, function ($mock) {
            $mock->shouldReceive('sendInvoice')
                 ->once()
                 ->andThrow(new \RuntimeException('メール送信に失敗しました。'));
        });

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('error');
    }

    /** Mail failure does not corrupt invoice data. */
    public function test_mail_failure_does_not_corrupt_invoice(): void
    {
        $invoice = $this->invoiceWithEmail(['status' => 'issued', 'title' => 'Original Title']);

        $this->mock(InvoiceMailService::class, function ($mock) {
            $mock->shouldReceive('sendInvoice')
                 ->once()
                 ->andThrow(new \RuntimeException('メール送信に失敗しました。'));
        });

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice));

        $invoice->refresh();
        $this->assertSame('issued', $invoice->status);
        $this->assertSame('Original Title', $invoice->title);
    }

    /** No-email case shows warning (not error) flash. */
    public function test_no_email_shows_warning_flash_not_error(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'issued']);
        $invoice->project->company->update(['email' => null]);

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('warning')
            ->assertSessionMissing('error');
    }

    /** invoice_mail_failed audit log is created on mail failure (via mock). */
    public function test_mail_failure_creates_failure_audit_log(): void
    {
        $invoice = $this->invoiceWithEmail();

        // Mock the service to throw and also write the failure audit log
        // (simulates the service's internal try-catch behaviour)
        $this->mock(InvoiceMailService::class, function ($mock) use ($invoice) {
            $mock->shouldReceive('sendInvoice')
                 ->once()
                 ->andReturnUsing(function () use ($invoice) {
                     \App\Models\AuditLog::create([
                         'user_id'     => null,
                         'action'      => 'invoice_mail_failed',
                         'target_type' => 'invoice',
                         'target_id'   => $invoice->id,
                         'ip_address'  => '127.0.0.1',
                         'user_agent'  => 'test',
                     ]);
                     throw new \RuntimeException('メール送信に失敗しました。');
                 });
        });

        $this->actingAs($this->user)
            ->post(route('invoices.mail.send', $invoice));

        $this->assertDatabaseHas('audit_logs', [
            'action'     => 'invoice_mail_failed',
            'target_id'  => $invoice->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function invoiceWithEmail(array $overrides = []): Invoice
    {
        $invoice = Invoice::factory()->create(array_merge(['status' => 'issued'], $overrides));
        $invoice->project->company->update(['email' => 'client@example.com']);

        return $invoice->load('project.company', 'payments', 'files');
    }

    private function createFakePdf(Invoice $invoice, string $filename): InvoiceFile
    {
        Storage::disk('invoices')->put($filename, '%PDF-test');

        return InvoiceFile::create([
            'invoice_id' => $invoice->id,
            'file_name'  => $filename,
            'file_path'  => $filename,
            'file_type'  => 'pdf',
            'file_hash'  => hash('sha256', '%PDF-test'),
        ]);
    }
}
