<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ParceiroController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::delete('/propostas/{id}', [DashboardController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('propostas.destroy');

Route::get('/propostas/{id}/editar', [DashboardController::class, 'edit'])
    ->middleware(['auth'])
    ->name('propostas.edit');

Route::put('/propostas/{id}', [DashboardController::class, 'update'])
    ->middleware(['auth'])
    ->name('propostas.update');

Route::get('/propostas/criar', [DashboardController::class, 'create'])
    ->middleware(['auth'])
    ->name('propostas.create');

Route::post('/propostas', [DashboardController::class, 'store'])
    ->middleware(['auth'])
    ->name('propostas.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');    
    Route::get('/admin/exportar-csv', [AdminController::class, 'exportCsv'])->name('admin.export.csv');
    Route::resource('clientes', ClienteController::class);
    Route::resource('parceiros', ParceiroController::class);
    Route::resource('produtos', ProdutoController::class);
    Route::resource('users', UserController::class);
});

Route::get('/auth/mfa/status/{transactionId}', [MfaController::class, 'checkStatus'])
    ->middleware('web')
    ->name('auth.mfa.status');

require __DIR__.'/auth.php';