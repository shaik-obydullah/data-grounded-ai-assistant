<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CsvImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('imports.index');
});

Route::resource('imports', CsvImportController::class)->only(['index', 'store', 'show']);
Route::post('imports/{import}/process', [CsvImportController::class, 'process'])->name('imports.process');
Route::get('imports/{import}/progress', [CsvImportController::class, 'progress'])->name('imports.progress');
Route::resource('companies', CompanyController::class)->only(['index', 'edit', 'update', 'show']);
Route::controller(AiController::class)->prefix('ai')->name('ai.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/ask', 'ask')->name('ask');
    Route::get('/history', 'history')->name('history');
    Route::post('/clear', 'clear')->name('clear');
    Route::post('/pull-model', 'pullModel')->name('pull-model');
});
