<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentService
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function create(Invoice $invoice, array $data, Request $request): Payment
    {
        $data['invoice_id'] = $invoice->id;
        $payment = Payment::create($data);

        $this->auditLogService->log(
            action: 'payment_created',
            targetType: 'payment',
            targetId: $payment->id,
            request: $request,
        );

        $this->recalculateInvoiceStatus($invoice->fresh(), $request);

        return $payment;
    }

    public function update(Payment $payment, array $data, Request $request): Payment
    {
        $payment->update($data);

        $this->auditLogService->log(
            action: 'payment_updated',
            targetType: 'payment',
            targetId: $payment->id,
            request: $request,
        );

        $this->recalculateInvoiceStatus($payment->invoice->fresh(), $request);

        return $payment;
    }

    public function delete(Payment $payment, Request $request): void
    {
        $invoice = $payment->invoice;

        $this->auditLogService->log(
            action: 'payment_deleted',
            targetType: 'payment',
            targetId: $payment->id,
            request: $request,
        );

        $payment->delete();

        $this->recalculateInvoiceStatus($invoice->fresh(), $request);
    }

    /**
     * Derive invoice status from total payments and update if it changed.
     *
     *   cancelled               → skip (never auto-update)
     *   totalPaid >= total_amount → paid
     *   totalPaid > 0             → issued
     *   totalPaid = 0, was paid   → issued
     *   totalPaid = 0, otherwise  → keep current status
     */
    private function recalculateInvoiceStatus(Invoice $invoice, Request $request): void
    {
        if ($invoice->status === 'cancelled') {
            return;
        }

        $totalPaid = $invoice->payments()->sum('amount');
        $previous  = $invoice->status;

        if ($totalPaid >= $invoice->total_amount) {
            $newStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $newStatus = 'issued';
        } else {
            $newStatus = $previous === 'paid' ? 'issued' : $previous;
        }

        if ($newStatus === $previous) {
            return;
        }

        $invoice->update(['status' => $newStatus]);

        $action = match ($newStatus) {
            'paid'   => 'invoice_status_updated_to_paid',
            'issued' => 'invoice_status_updated_to_issued',
            default  => null,
        };

        if ($action) {
            $this->auditLogService->log(
                action: $action,
                targetType: 'invoice',
                targetId: $invoice->id,
                request: $request,
            );
        }
    }
}
