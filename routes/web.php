<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContractTemplateController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceFileController;
use App\Http\Controllers\InvoiceMailController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractEvidenceExportController;
use App\Http\Controllers\ContractFileController;
use App\Http\Controllers\ContractMailController;
use App\Http\Controllers\ContractSigningController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// External consent routes — no authentication required
Route::prefix('sign/contracts')->name('sign.contracts.')->group(function () {
    Route::get('{token}', [ConsentController::class, 'show'])->name('show');
    Route::post('{token}', [ConsentController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('companies', CompanyController::class);
    Route::resource('contract-templates', ContractTemplateController::class)->except(['show']);
    Route::get('contract-templates/{contractTemplate}/preview', [ContractTemplateController::class, 'preview'])
        ->name('contract-templates.preview');
    Route::resource('projects', ProjectController::class);
    Route::resource('contracts', ContractController::class);
    Route::resource('invoices', InvoiceController::class);

    // Invoice file: PDF generation and download
    Route::post('invoices/{invoice}/pdf', [InvoiceFileController::class, 'store'])
        ->name('invoices.pdf.store');
    Route::get('invoices/{invoice}/files/{invoiceFile}/download', [InvoiceFileController::class, 'download'])
        ->name('invoices.files.download');

    // Invoice mail: send invoice PDF to customer
    Route::post('invoices/{invoice}/send-mail', [InvoiceMailController::class, 'send'])
        ->name('invoices.mail.send');

    // Payments: nested under invoice for store, standalone for edit/update/destroy
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])
        ->name('invoices.payments.store');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])
        ->name('payments.edit');
    Route::put('payments/{payment}', [PaymentController::class, 'update'])
        ->name('payments.update');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])
        ->name('payments.destroy');

    // Contract evidence exports: ZIP generation and download
    Route::post('contracts/{contract}/evidence-exports', [ContractEvidenceExportController::class, 'store'])
        ->name('contracts.evidence-exports.store');
    Route::get('contracts/{contract}/evidence-exports/{export}/download', [ContractEvidenceExportController::class, 'download'])
        ->name('contracts.evidence-exports.download');

    // Contract file: PDF generation, download, and hash verification
    Route::post('contracts/{contract}/pdf', [ContractFileController::class, 'store'])
        ->name('contracts.pdf.store');
    Route::get('contracts/{contract}/files/{contractFile}/download', [ContractFileController::class, 'download'])
        ->name('contracts.files.download');
    Route::post('contracts/{contract}/files/{contractFile}/verify-hash', [ContractFileController::class, 'verifyHash'])
        ->name('contracts.files.verify-hash');

    // Contract signing: generate sign URL (admin only)
    Route::post('contracts/{contract}/generate-sign-url', [ContractSigningController::class, 'generate'])
        ->name('contracts.sign-url.generate');

    // Contract mail: send consent URL email (admin only)
    Route::post('contracts/{contract}/send-sign-request', [ContractMailController::class, 'sendSignRequest'])
        ->name('contracts.mail.send-sign-request');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
