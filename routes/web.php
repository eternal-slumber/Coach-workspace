<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DuplicateScheduledTrainingController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\RescheduleScheduledTrainingController;
use App\Http\Controllers\ScheduledTrainingController;
use App\Http\Controllers\TraineeController;
use App\Http\Controllers\TrainingGroupController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('calendar', CalendarController::class)->name('calendar');
    Route::patch(
        'scheduled-trainings/{scheduled_training}/schedule',
        RescheduleScheduledTrainingController::class,
    )->name('scheduled-trainings.schedule');
    Route::post(
        'scheduled-trainings/{scheduled_training}/duplicate',
        DuplicateScheduledTrainingController::class,
    )->name('scheduled-trainings.duplicate');
    Route::resource('trainees', TraineeController::class);
    Route::resource('training-groups', TrainingGroupController::class);
    Route::resource('scheduled-trainings', ScheduledTrainingController::class);
    Route::resource('exercises', ExerciseController::class);
});

require __DIR__.'/settings.php';
