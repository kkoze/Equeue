<?php

use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('queue.index');
});

Route::get('print', [QueueController::class, 'print'])->name('queue.print');
// Queue management routes
Route::prefix('queue')->name('queue.')->group(function () {
    Route::get('/', [QueueController::class, 'index'])->name('index');
    Route::get('/create', [QueueController::class, 'create'])->name('create');
    Route::post('/', [QueueController::class, 'store'])->name('store');
    Route::get('/{queue}', [QueueController::class, 'show'])->name('show');
    
    // API routes for AJAX calls
    Route::post('/call-next', [QueueController::class, 'callNext'])->name('call-next');
    Route::patch('/{queue}/status', [QueueController::class, 'updateStatus'])->name('update-status');
    
    // Admin routes
    Route::delete('/reset', [QueueController::class, 'reset'])->name('reset');
});