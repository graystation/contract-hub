<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePhase3BTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // fmt_amount helper
    // -------------------------------------------------------------------------

    public function test_fmt_amount_formats_with_yen_sign_and_commas(): void
    {
        $this->assertSame('¥50,000', fmt_amount(50000));
        $this->assertSame('¥1,234,567', fmt_amount(1234567));
        $this->assertSame('¥0', fmt_amount(0));
        $this->assertSame('¥0', fmt_amount(null));
    }

    // -------------------------------------------------------------------------
    // Invoice model accessors
    // -------------------------------------------------------------------------

    public function test_paid_amount_returns_sum_of_payments(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 50000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 30000]);

        $this->assertSame(80000, $invoice->paid_amount);
    }

    public function test_unpaid_amount_returns_remaining_balance(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 40000]);

        $this->assertSame(70000, $invoice->unpaid_amount);
    }

    public function test_unpaid_amount_never_negative(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 200000]);

        $this->assertSame(0, $invoice->unpaid_amount);
    }

    public function test_overpaid_amount_returns_excess(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 120000]);

        $this->assertSame(10000, $invoice->overpaid_amount);
    }

    public function test_is_partially_paid_when_partial_payment(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 50000]);

        $this->assertTrue($invoice->is_partially_paid);
    }

    public function test_is_not_partially_paid_when_no_payments(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);

        $this->assertFalse($invoice->is_partially_paid);
    }

    public function test_is_overpaid_when_payments_exceed_total(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 120000]);

        $this->assertTrue($invoice->is_overpaid);
    }

    public function test_is_fully_paid_when_exact_payment(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 110000]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 110000]);

        $this->assertTrue($invoice->is_fully_paid);
    }

    public function test_is_overdue_when_past_due_date_and_unpaid(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 110000,
            'due_date'     => now()->subDays(2),
            'status'       => 'issued',
        ]);

        $this->assertTrue($invoice->is_overdue);
    }

    public function test_is_not_overdue_when_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 110000,
            'due_date'     => now()->subDays(2),
            'status'       => 'paid',
        ]);

        $this->assertFalse($invoice->is_overdue);
    }

    public function test_is_not_overdue_when_due_date_is_today(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 110000,
            'due_date'     => today(),
            'status'       => 'issued',
        ]);

        $this->assertFalse($invoice->is_overdue);
    }

    public function test_is_due_soon_within_7_days(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 110000,
            'due_date'     => now()->addDays(5),
            'status'       => 'issued',
        ]);

        $this->assertTrue($invoice->is_due_soon);
    }

    public function test_is_not_due_soon_when_more_than_7_days(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 110000,
            'due_date'     => now()->addDays(10),
            'status'       => 'issued',
        ]);

        $this->assertFalse($invoice->is_due_soon);
    }

    public function test_is_not_due_soon_when_fully_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 110000,
            'due_date'     => now()->addDays(3),
            'status'       => 'paid',
        ]);

        $this->assertFalse($invoice->is_due_soon);
    }

    // -------------------------------------------------------------------------
    // Cancelled invoice status is not auto-updated
    // -------------------------------------------------------------------------

    public function test_cancelled_invoice_status_not_changed_by_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'amount'       => 100000,
            'tax_amount'   => 10000,
            'total_amount' => 110000,
            'status'       => 'cancelled',
        ]);

        $this->actingAs($this->user)
            ->post(route('invoices.payments.store', $invoice), [
                'amount'  => 110000,
                'paid_at' => '2026-06-01',
                'method'  => 'bank_transfer',
            ]);

        $this->assertSame('cancelled', $invoice->fresh()->status);
    }

    // -------------------------------------------------------------------------
    // Dashboard monthly stats
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_monthly_invoice_stats(): void
    {
        // Invoice issued this month
        Invoice::factory()->create([
            'total_amount' => 110000,
            'status'       => 'issued',
            'issued_at'    => now()->startOfMonth(),
        ]);

        // Invoice issued last month (should NOT appear in monthly stats)
        Invoice::factory()->create([
            'total_amount' => 220000,
            'status'       => 'issued',
            'issued_at'    => now()->subMonth(),
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('今月の請求・入金')
            ->assertSee('¥110,000'); // this month's invoice
    }

    // -------------------------------------------------------------------------
    // Dashboard unpaid invoice list
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_unpaid_invoice_list(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 110000,
            'status'       => 'issued',
            'due_date'     => now()->addDays(5),
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('未入金請求一覧')
            ->assertSee($invoice->invoice_number);
    }

    public function test_dashboard_does_not_show_paid_invoices_in_unpaid_list(): void
    {
        $paid = Invoice::factory()->create([
            'total_amount' => 110000,
            'status'       => 'paid',
        ]);
        Payment::factory()->create(['invoice_id' => $paid->id, 'amount' => 110000]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        // If unpaid_invoice_list is empty, the section won't show at all
        if ($response->getContent() !== '' && str_contains($response->getContent(), '未入金請求一覧')) {
            $response->assertDontSee($paid->invoice_number);
        } else {
            $this->assertTrue(true); // Section hidden when empty — correct behavior
        }
    }

    // -------------------------------------------------------------------------
    // Payment status transitions (extended)
    // -------------------------------------------------------------------------

    public function test_payment_update_changes_status_to_paid(): void
    {
        $invoice = Invoice::factory()->create([
            'amount'       => 100000,
            'tax_amount'   => 10000,
            'total_amount' => 110000,
            'status'       => 'issued',
        ]);
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount'     => 50000,
        ]);

        // Update payment to cover the full amount
        $this->actingAs($this->user)
            ->put(route('payments.update', $payment), [
                'amount'  => 110000,
                'paid_at' => '2026-06-01',
                'method'  => 'bank_transfer',
            ]);

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_payment_delete_reverts_paid_to_issued_when_balance_remains(): void
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
}
