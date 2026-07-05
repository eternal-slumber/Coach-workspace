<?php

use App\Models\Trainee;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user owns trainees', function () {
    $user = User::factory()->create();

    $trainee = $user->trainees()->create([
        'name' => 'Алексей Смирнов',
        'age' => 28,
        'level' => 'Начинающий',
        'goal' => 'Улучшить общую физическую форму',
    ]);

    expect($trainee)
        ->toBeInstanceOf(Trainee::class)
        ->age->toBe(28)
        ->and($trainee->user->is($user))->toBeTrue()
        ->and($user->trainees->contains($trainee))->toBeTrue();
});

test('a user owns training groups', function () {
    $user = User::factory()->create();

    $trainingGroup = $user->trainingGroups()->create([
        'name' => 'Утренняя группа',
        'sport_type' => 'Функциональный тренинг',
        'age_range' => '25–40 лет',
        'level' => 'Средний',
        'goal' => 'Развить выносливость',
    ]);

    expect($trainingGroup)
        ->toBeInstanceOf(TrainingGroup::class)
        ->and($trainingGroup->user->is($user))->toBeTrue()
        ->and($user->trainingGroups->contains($trainingGroup))->toBeTrue();
});

test('deleting a user deletes owned trainees and training groups', function () {
    $user = User::factory()->create();

    $trainee = $user->trainees()->create([
        'name' => 'Мария Иванова',
        'level' => 'Продвинутый',
        'goal' => 'Подготовиться к соревнованиям',
    ]);

    $trainingGroup = $user->trainingGroups()->create([
        'name' => 'Вечерняя группа',
        'sport_type' => 'Силовой тренинг',
        'age_range' => '18–35 лет',
        'level' => 'Продвинутый',
        'goal' => 'Увеличить силовые показатели',
    ]);

    $user->delete();

    $this->assertModelMissing($trainee);
    $this->assertModelMissing($trainingGroup);
});
