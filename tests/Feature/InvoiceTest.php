<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // Invoice CRUD
    // -------------------------------------------------------------------------

    public function test_invoice_index_is_accessible(): void
    {
        Invoice::factory()->create();

        $this->actingAs($this->user)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertViewIs('invoices.index');
    }

    public function test_invoice_can_be_created(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.store'), [
                'project_id' => $project->id,
                'title'      => 'テスト請求書',
                'amount'     => 100000,
                'status'     => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', ['title' => 'テスト請求書']);
    }

    public function test_invoice_number_is_auto_generated(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.store'), [
                'project_id' => $project->id,
                'title'      => '自動採番テスト',
                'amount'     => 50000,
                'status'     => 'draft',
            ]);

        $invoice = Invoice::where('title', '自動採番テスト')->first();
        $this->assertNotNull($invoice->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_tax_amounts_are_calculated_correctly(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.store'), [
                'project_id' => $project->id,
                'title'      => '税計算テスト',
                'amount'     => 200000,
                'status'     => 'draft',
            ]);

        $invoice = Invoice::where('title', '税計算テスト')->first();
        $this->assertSame(20000, $invoice->tax_amount);
        $this->assertSame(220000, $invoice->total_amount);
    }

    public function test_invoice_can_be_updated(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($this->user)
            ->put(route('invoices.update', $invoice), [
                'project_id' => $invoice->project_id,
                'title'      => '更新後タイトル',
                'amount'     => $invoice->amount,
                'status'     => $invoice->status,
            ])
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'title' => '更新後タイトル']);
    }

    public function test_invoice_can_be_deleted(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('invoices.destroy', $invoice))
            ->assertRedirect(route('invoices.index'));

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    // -------------------------------------------------------------------------
    // Payment CRUD
    // -------------------------------------------------------------------------

    public function test_payment_can_be_created(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'issued']);

        $this->actingAs($this->user)
            ->post(route('invoices.payments.store', $invoice), [
                'amount'  => 50000,
                'paid_at' => '2026-06-01',
                'method'  => 'bank_transfer',
            ])
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 50000]);
    }

    public function test_payment_can_be_updated(): void
    {
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 10000]);

        $this->actingAs($this->user)
            ->put(route('payments.update', $payment), [
                'amount'  => 20000,
                'paid_at' => '2026-06-01',
                'method'  => 'cash',
            ])
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'amount' => 20000]);
    }

    public function test_payment_can_be_deleted(): void
    {
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

        $this->actingAs($this->user)
            ->delete(route('payments.destroy', $payment))
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    // -------------------------------------------------------------------------
    // Status auto-update
    // -------------------------------------------------------------------------

    public function test_invoice_status_becomes_paid_when_fully_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'amount'       => 100000,
            'tax_amount'   => 10000,
            'total_amount' => 110000,
            'status'       => 'issued',
        ]);

        $this->actingAs($this->user)
            ->post(route('invoices.payments.store', $invoice), [
                'amount'  => 110000,
                'paid_at' => '2026-06-01',
                'method'  => 'bank_transfer',
            ]);

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_invoice_status_reverts_to_issued_when_payment_deleted(): void
    {
        $invoice = Invoice::factory()->create([
            'amount'       => 100000,
            'tax_amount'   => 10000,
            'total_amount' => 110000,
            'status'       => 'paid',
        ]);
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount'     => 110000,
        ]);

        $this->actingAs($this->user)
            ->delete(route('payments.destroy', $payment));

        $this->assertSame('issued', $invoice->fresh()->status);
    }

    public function test_partial_payment_sets_status_to_issued(): void
    {
        $invoice = Invoice::factory()->create([
            'amount'       => 100000,
            'tax_amount'   => 10000,
            'total_amount' => 110000,
            'status'       => 'draft',
        ]);

        $this->actingAs($this->user)
            ->post(route('invoices.payments.store', $invoice), [
                'amount'  => 50000,
                'paid_at' => '2026-06-01',
                'method'  => 'bank_transfer',
            ]);

        $this->assertSame('issued', $invoice->fresh()->status);
    }

    // -------------------------------------------------------------------------
    // AuditLog
    // -------------------------------------------------------------------------

    public function test_invoice_creation_creates_audit_log(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->post(route('invoices.store'), [
                'project_id' => $project->id,
                'title'      => '監査ログテスト',
                'amount'     => 100000,
                'status'     => 'draft',
            ]);

        $invoice = Invoice::where('title', '監査ログテスト')->first();
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice_created', 'target_id' => $invoice->id]);
    }

    public function test_payment_creation_creates_audit_log(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'issued']);

        $this->actingAs($this->user)
            ->post(route('invoices.payments.store', $invoice), [
                'amount'  => 50000,
                'paid_at' => '2026-06-01',
                'method'  => 'bank_transfer',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'payment_created',
            'target_type' => 'payment',
        ]);
    }

    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_invoice_stats(): void
    {
        Invoice::factory()->create(['total_amount' => 110000, 'status' => 'issued']);
        Invoice::factory()->create(['total_amount' => 220000, 'status' => 'paid']);

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('請求・入金サマリー')
            ->assertSee('¥330,000');  // total invoiced
    }
}
