<?php

use App\Http\Controllers\TraineeController;
use App\Http\Controllers\TrainingGroupController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::apiResource('trainees', TraineeController::class);
    Route::apiResource('training-groups', TrainingGroupController::class);
});

require __DIR__.'/settings.php';
