<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractFileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('companies', CompanyController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('contracts', ContractController::class);

    // Contract file: PDF generation, download, and hash verification
    Route::post('contracts/{contract}/pdf', [ContractFileController::class, 'store'])
        ->name('contracts.pdf.store');
    Route::get('contracts/{contract}/files/{contractFile}/download', [ContractFileController::class, 'download'])
        ->name('contracts.files.download');
    Route::post('contracts/{contract}/files/{contractFile}/verify-hash', [ContractFileController::class, 'verifyHash'])
        ->name('contracts.files.verify-hash');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
