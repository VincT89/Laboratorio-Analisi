<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\SampleFileController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Rotte pubbliche
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

// Homepage pubblica — reindirizza al login o ai campioni
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('samples.index');
    }
    return view('welcome');
})->name('home');

// Dashboard principale
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rotte protette — richiedono autenticazione
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    | Profilo Personale
    */
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    | Staff
    */
    Route::resource('staff', \App\Http\Controllers\StaffController::class)->except('show');

    /*
    | Tipi di Campione (Catalogo Matrici)
    */
    Route::resource('sample-types', \App\Http\Controllers\SampleTypeController::class)->except(['show', 'destroy']);
    Route::patch('sample-types/{sampleType}/activate', [\App\Http\Controllers\SampleTypeController::class, 'activate'])->name('sample-types.activate');
    Route::patch('sample-types/{sampleType}/deactivate', [\App\Http\Controllers\SampleTypeController::class, 'deactivate'])->name('sample-types.deactivate');

    /*
    | Clienti
    */
    Route::resource('clients', ClientController::class);
    Route::patch('clients/{client}/archive', [ClientController::class, 'archive'])
        ->name('clients.archive');
    Route::patch('clients/{client}/restore', [ClientController::class, 'restore'])
        ->name('clients.restore');
    Route::get('clients-archived', [ClientController::class, 'archived'])
        ->name('clients.archived');

    /*
    | Campioni
    */
    Route::resource('samples', SampleController::class);
    Route::patch('samples/{sample}/accept', [SampleController::class, 'accept'])
        ->name('samples.accept');
    Route::patch('samples/{sample}/complete', [SampleController::class, 'complete'])
        ->name('samples.complete');
    Route::patch('samples/{sample}/archive', [SampleController::class, 'archive'])
        ->name('samples.archive');
    Route::patch('samples/{sample}/restore', [SampleController::class, 'restore'])
        ->name('samples.restore');
    Route::get('samples-archived', [SampleController::class, 'archived'])
        ->name('samples.archived');

    /*
    | File campioni
    */
    Route::prefix('samples/{sample}/files')->name('samples.files.')->group(function () {
        Route::post('/', [SampleFileController::class, 'store'])
            ->name('store');
        Route::get('{sampleFile}/download', [SampleFileController::class, 'download'])
            ->name('download');
        Route::patch('{sampleFile}/archive', [SampleFileController::class, 'archive'])
            ->name('archive');
        Route::delete('{sampleFile}', [SampleFileController::class, 'destroy'])
            ->name('destroy');
    });

});