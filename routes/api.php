<?php

use App\Http\Controllers\Api\GenerateTasksController;
use Illuminate\Support\Facades\Route;

Route::post('/generate-tasks', GenerateTasksController::class);
