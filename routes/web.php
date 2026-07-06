<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduledTrainingController;
use App\Http\Controllers\TraineeController;
use App\Http\Controllers\TrainingGroupController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::apiResource('trainees', TraineeController::class);
    Route::apiResource('training-groups', TrainingGroupController::class);
    Route::apiResource('scheduled-trainings', ScheduledTrainingController::class);
});

require __DIR__.'/settings.php';
