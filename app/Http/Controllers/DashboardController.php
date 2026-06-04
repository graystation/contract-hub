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
        $invoiceTotalAmount = Invoice::whereNotIn('status', ['cancelled'])->sum('total_amount');
        $paymentTotalAmount = Payment::sum('amount');

        // Remaining balance on each unpaid invoice
        $unpaidAmount = Invoice::whereIn('status', ['draft', 'issued'])
            ->withSum('payments', 'amount')
            ->get()
            ->sum(fn ($inv) => max(0, $inv->total_amount - ($inv->payments_sum_amount ?? 0)));

        return view('dashboard', [
            'companyCount'       => Company::count(),
            'projectCount'       => Project::count(),
            'contractCount'      => Contract::count(),
            'invoiceTotalAmount' => $invoiceTotalAmount,
            'paymentTotalAmount' => $paymentTotalAmount,
            'unpaidAmount'       => $unpaidAmount,
            'unpaidCount'        => Invoice::whereIn('status', ['draft', 'issued'])->count(),
        ]);
    }
}
