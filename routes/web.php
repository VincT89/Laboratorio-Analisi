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
    | Tipi di Contenitore (Backoffice Lab)
    */
    Route::resource('container-types', \App\Http\Controllers\ContainerTypeController::class)->except(['show', 'destroy']);
    Route::patch('container-types/{containerType}/activate', [\App\Http\Controllers\ContainerTypeController::class, 'activate'])->name('container-types.activate');
    Route::patch('container-types/{containerType}/deactivate', [\App\Http\Controllers\ContainerTypeController::class, 'deactivate'])->name('container-types.deactivate');

    /*
    | Unità di Misura (Backoffice Lab)
    */
    Route::resource('measurement-units', \App\Http\Controllers\MeasurementUnitController::class)->except(['show', 'destroy']);
    Route::patch('measurement-units/{measurementUnit}/activate', [\App\Http\Controllers\MeasurementUnitController::class, 'activate'])->name('measurement-units.activate');
    Route::patch('measurement-units/{measurementUnit}/deactivate', [\App\Http\Controllers\MeasurementUnitController::class, 'deactivate'])->name('measurement-units.deactivate');

    /*
    | Stati di Conservazione (Backoffice Lab)
    */
    Route::resource('conservation-statuses', \App\Http\Controllers\ConservationStatusController::class)->except(['show', 'destroy']);
    Route::patch('conservation-statuses/{conservationStatus}/activate', [\App\Http\Controllers\ConservationStatusController::class, 'activate'])->name('conservation-statuses.activate');
    Route::patch('conservation-statuses/{conservationStatus}/deactivate', [\App\Http\Controllers\ConservationStatusController::class, 'deactivate'])->name('conservation-statuses.deactivate');

    /*
    | Clienti
    */
    Route::get('clients/search', [ClientController::class, 'search'])
        ->name('clients.search');
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
    Route::patch('samples/{sample}/reject', [SampleController::class, 'reject'])
        ->name('samples.reject');
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