<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TaskController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::get('/{id}', [TaskController::class, 'show'])->name('editTask');
    // Route::get('/{id}', [TaskController::class, 'edit'])->name('editTask');
    Route::put('/{id}', [TaskController::class, 'update'])->name('updateTask');
    Route::delete('/{id}', [TaskController::class, 'destroy'])->name('delete');
    Route::put('/tasks/{id}/status', [TaskController::class, 'updateTaskStatus'])->name('updateStatus');


});
