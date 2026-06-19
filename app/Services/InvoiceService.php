<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    private const TAX_RATE = 0.10;

    public function __construct(private AuditLogService $auditLogService) {}

    public function paginate(int $perPage = 15, string $sort = 'created_at', string $dir = 'desc'): LengthAwarePaginator
    {
        $allowed = ['invoice_number', 'status', 'total_amount', 'issued_at', 'due_date', 'created_at'];
        $sort    = in_array($sort, $allowed) ? $sort : 'created_at';
        $dir     = $dir === 'asc' ? 'asc' : 'desc';

        return Invoice::with('project.company', 'payments')
            ->orderBy($sort, $dir)
            ->paginate($perPage);
    }

    public function create(array $data, Request $request): Invoice
    {
        $data = $this->withTaxCalculated($data);
        $data['invoice_number'] = $this->generateInvoiceNumber();

        $invoice = Invoice::create($data);

        $this->auditLogService->log(
            action: 'invoice_created',
            targetType: 'invoice',
            targetId: $invoice->id,
            request: $request,
        );

        return $invoice;
    }

    public function update(Invoice $invoice, array $data, Request $request): Invoice
    {
        $data = $this->withTaxCalculated($data);
        $invoice->update($data);

        $this->auditLogService->log(
            action: 'invoice_updated',
            targetType: 'invoice',
            targetId: $invoice->id,
            request: $request,
        );

        return $invoice;
    }

    public function delete(Invoice $invoice, Request $request): void
    {
        $this->auditLogService->log(
            action: 'invoice_deleted',
            targetType: 'invoice',
            targetId: $invoice->id,
            request: $request,
        );

        $invoice->delete();
    }

    public function generateInvoiceNumber(): string
    {
        $year  = now()->format('Y');
        $count = Invoice::whereYear('created_at', $year)->count() + 1;

        return sprintf('INV-%s-%04d', $year, $count);
    }

    /** Append tax_amount and total_amount based on amount at 10% tax rate. */
    private function withTaxCalculated(array $data): array
    {
        $amount            = (int) $data['amount'];
        $data['tax_amount']   = (int) round($amount * self::TAX_RATE);
        $data['total_amount'] = $amount + $data['tax_amount'];

        return $data;
    }
}
