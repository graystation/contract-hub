<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        // ------------------------------------------------------------------
        // All-time billing stats
        // ------------------------------------------------------------------
        $allInvoices        = Invoice::with('payments')->whereNotIn('status', ['cancelled'])->get();
        $invoiceTotalAmount = $allInvoices->sum('total_amount');
        $paymentTotalAmount = $allInvoices->sum('paid_amount');
        $unpaidAmount       = $allInvoices->sum('unpaid_amount');
        $unpaidCount        = Invoice::whereIn('status', ['draft', 'issued'])->count();

        // ------------------------------------------------------------------
        // This-month billing stats (based on issued_at)
        // ------------------------------------------------------------------
        $thisYear  = now()->year;
        $thisMonth = now()->month;

        $monthlyInvoices        = Invoice::with('payments')
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('issued_at', $thisYear)
            ->whereMonth('issued_at', $thisMonth)
            ->get();

        $monthlyInvoiceTotal = $monthlyInvoices->sum('total_amount');
        $monthlyPaymentTotal = $monthlyInvoices->sum('paid_amount');
        $monthlyUnpaidTotal  = max(0, $monthlyInvoiceTotal - $monthlyPaymentTotal);
        $monthlyInvoiceCount = $monthlyInvoices->count();

        // ------------------------------------------------------------------
        // Unpaid invoice list (up to 10, due soonest first)
        // ------------------------------------------------------------------
        $unpaidInvoiceList = Invoice::with('project.company', 'payments')
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END ASC, due_date ASC')
            ->get()
            ->filter(fn ($inv) => $inv->unpaid_amount > 0)
            ->take(10);

        return view('dashboard', [
            'companyCount'       => Company::count(),
            'projectCount'       => Project::count(),
            'contractCount'      => Contract::count(),
            // All-time
            'invoiceTotalAmount' => $invoiceTotalAmount,
            'paymentTotalAmount' => $paymentTotalAmount,
            'unpaidAmount'       => $unpaidAmount,
            'unpaidCount'        => $unpaidCount,
            // This month
            'monthlyInvoiceTotal' => $monthlyInvoiceTotal,
            'monthlyPaymentTotal' => $monthlyPaymentTotal,
            'monthlyUnpaidTotal'  => $monthlyUnpaidTotal,
            'monthlyInvoiceCount' => $monthlyInvoiceCount,
            // Unpaid list
            'unpaidInvoiceList'   => $unpaidInvoiceList,
        ]);
    }
}
