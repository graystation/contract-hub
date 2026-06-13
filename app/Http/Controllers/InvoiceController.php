<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\InvoiceFileService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $service,
        private InvoiceFileService $fileService,
    ) {}

    public function index()
    {
        return view('invoices.index', [
            'invoices' => $this->service->paginate(),
        ]);
    }

    public function create()
    {
        return view('invoices.create', [
            'projects'       => Project::with('company')->orderByDesc('created_at')->get(),
            'statuses'       => Invoice::STATUSES,
            'invoiceNumber'  => $this->service->generateInvoiceNumber(),
        ]);
    }

    public function store(InvoiceRequest $request)
    {
        $invoice = $this->service->create($request->validated(), $request);

        return redirect()->route('invoices.show', $invoice)->with('success', '請求書を作成しました。');
    }

    public function show(Invoice $invoice, Request $request)
    {
        $invoice->load('project.company', 'payments', 'files');

        $latestPdf  = $this->fileService->getLatestPdf($invoice);
        $paymentIds = $invoice->payments->pluck('id');

        $auditLogs = AuditLog::where(function ($q) use ($invoice, $paymentIds) {
            $q->where('target_type', 'invoice')->where('target_id', $invoice->id);
            if ($paymentIds->isNotEmpty()) {
                $q->orWhere(function ($q2) use ($paymentIds) {
                    $q2->where('target_type', 'payment')->whereIn('target_id', $paymentIds);
                });
            }
        })->latest()->limit(20)->get();

        return view('invoices.show', compact('invoice', 'latestPdf', 'auditLogs'));
    }

    public function edit(Invoice $invoice)
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled'], true),
            403,
            '入金済み・キャンセル済みの請求書は編集できません。',
        );

        return view('invoices.edit', [
            'invoice'  => $invoice,
            'projects' => Project::with('company')->orderByDesc('created_at')->get(),
            'statuses' => Invoice::STATUSES,
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled'], true),
            403,
            '入金済み・キャンセル済みの請求書は編集できません。',
        );

        $this->service->update($invoice, $request->validated(), $request);

        return redirect()->route('invoices.show', $invoice)->with('success', '請求書を更新しました。');
    }

    public function destroy(Invoice $invoice, Request $request)
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled'], true) || $invoice->payments()->exists(),
            403,
            '入金記録のある、または入金済み・キャンセル済みの請求書は削除できません。',
        );

        $this->service->delete($invoice, $request);

        return redirect()->route('invoices.index')->with('success', '請求書を削除しました。');
    }
}
