<?php

use App\Http\Controllers\ClientBriefTasksController;
use App\Http\Controllers\GeneratedTasksController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tasks', [GeneratedTasksController::class, 'index'])->name('generated-tasks.index');
Route::patch('/tasks/{generated_task}', [GeneratedTasksController::class, 'update'])->name('generated-tasks.update');

Route::get('/client-brief', [ClientBriefTasksController::class, 'create'])->name('client-brief.create');
Route::post('/client-brief', [ClientBriefTasksController::class, 'store'])->name('client-brief.store');
Route::post('/client-brief/clarify', [ClientBriefTasksController::class, 'clarify'])->name('client-brief.clarify');
