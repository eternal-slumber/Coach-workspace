<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->exercises() as $exercise) {
            $systemExercise = Exercise::query()->firstOrNew([
                'user_id' => null,
                'name' => $exercise['name'],
                'is_system' => true,
            ]);

            $systemExercise->forceFill([
                ...$exercise,
                'user_id' => null,
                'is_system' => true,
            ])->save();
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function exercises(): array
    {
        return [
            $this->exercise('Суставная разминка', 'Плавная проработка основных суставов сверху вниз.', 'Разминка', 'Лёгкая', 'Без инвентаря', 8, ['разминка', 'мобильность', 'низкая нагрузка']),
            $this->exercise('Лёгкий бег', 'Спокойный бег для повышения температуры тела и подготовки к нагрузке.', 'Разминка', 'Лёгкая', 'Без инвентаря', 10, ['разминка', 'выносливость', 'без инвентаря']),
            $this->exercise('Динамическая растяжка', 'Контролируемые махи и выпады в движении без длительной фиксации.', 'Мобильность', 'Лёгкая', 'Без инвентаря', 8, ['разминка', 'мобильность', 'без инвентаря']),
            $this->exercise('Координационная лестница', 'Серии быстрых шаговых комбинаций через координационную лестницу.', 'Координация', 'Средняя', 'Координационная лестница', 12, ['координация', 'скорость', 'дети']),
            $this->exercise('Змейка между конусами', 'Бег с контролируемой сменой направления между конусами.', 'Координация', 'Средняя', 'Конусы', 10, ['координация', 'скорость', 'дети']),
            $this->exercise('Баланс на одной ноге', 'Удержание устойчивого положения с постепенным усложнением.', 'Координация', 'Лёгкая', 'Без инвентаря', 6, ['координация', 'баланс', 'низкая нагрузка']),
            $this->exercise('Работа в парах на реакцию', 'Партнёр подаёт визуальные сигналы для быстрого выбора движения.', 'Реакция', 'Средняя', 'Конусы', 10, ['реакция', 'координация', 'работа в парах']),
            $this->exercise('Приседания с собственным весом', 'Приседания с нейтральным положением спины и контролем коленей.', 'Сила', 'Лёгкая', 'Без инвентаря', 10, ['силовая', 'офп', 'без инвентаря']),
            $this->exercise('Выпады назад', 'Попеременные выпады назад с сохранением устойчивого корпуса.', 'Сила', 'Средняя', 'Без инвентаря', 10, ['силовая', 'офп', 'ноги']),
            $this->exercise('Отжимания', 'Сгибание и разгибание рук в упоре с прямой линией корпуса.', 'Сила', 'Средняя', 'Без инвентаря', 8, ['силовая', 'офп', 'верх тела']),
            $this->exercise('Планка', 'Статическое удержание корпуса в упоре на предплечьях.', 'Стабилизация', 'Средняя', 'Коврик', 5, ['силовая', 'кор', 'без инвентаря']),
            $this->exercise('Ягодичный мост', 'Подъём таза лёжа с акцентом на ягодичные мышцы.', 'Сила', 'Лёгкая', 'Коврик', 8, ['силовая', 'офп', 'низкая нагрузка']),
            $this->exercise('Прыжки на месте', 'Серии мягких вертикальных прыжков с контролем приземления.', 'Скорость и сила', 'Средняя', 'Без инвентаря', 6, ['прыжки', 'скорость', 'дети']),
            $this->exercise('Бег с ускорениями', 'Короткие отрезки с плавным набором скорости и полным восстановлением.', 'Скорость', 'Высокая', 'Конусы', 15, ['скорость', 'бег', 'выносливость']),
            $this->exercise('Челночный бег', 'Повторные ускорения между отметками со сменой направления.', 'Скорость', 'Высокая', 'Конусы', 12, ['скорость', 'координация', 'офп']),
            $this->exercise('Интервальный бег', 'Чередование рабочих отрезков бега и активного восстановления.', 'Выносливость', 'Высокая', 'Без инвентаря', 20, ['выносливость', 'бег', 'офп']),
            $this->exercise('Передачи мяча в парах', 'Точные передачи мяча партнёру с постепенным увеличением дистанции.', 'Техника', 'Лёгкая', 'Мяч', 12, ['мяч', 'координация', 'работа в парах']),
            $this->exercise('Ведение мяча между конусами', 'Контролируемое ведение мяча по заданной траектории.', 'Техника', 'Средняя', 'Мяч и конусы', 12, ['мяч', 'координация', 'дети']),
            $this->exercise('Эстафета с изменением направления', 'Командная эстафета с ускорениями и обходом ориентиров.', 'Игровой блок', 'Средняя', 'Конусы', 15, ['игровой блок', 'скорость', 'дети']),
            $this->exercise('Игра «Зеркало»', 'Один участник повторяет движения партнёра в ограниченной зоне.', 'Игровой блок', 'Лёгкая', 'Без инвентаря', 10, ['игровой блок', 'реакция', 'работа в парах']),
            $this->exercise('Круговая ОФП', 'Последовательное выполнение базовых упражнений на нескольких станциях.', 'Общая физическая подготовка', 'Средняя', 'Коврик и конусы', 25, ['офп', 'силовая', 'выносливость']),
            $this->exercise('Мобилизация голеностопа', 'Плавное увеличение тыльного сгибания голеностопа у опоры.', 'Мобильность', 'Лёгкая', 'Без инвентаря', 6, ['мобильность', 'низкая нагрузка', 'голеностоп']),
            $this->exercise('Растяжка задней поверхности бедра', 'Мягкая статическая растяжка без пружинящих движений.', 'Заминка', 'Лёгкая', 'Коврик', 6, ['заминка', 'мобильность', 'низкая нагрузка']),
            $this->exercise('Дыхательное восстановление', 'Спокойное дыхание с постепенным снижением частоты движений.', 'Заминка', 'Лёгкая', 'Без инвентаря', 5, ['заминка', 'восстановление', 'низкая нагрузка']),
            $this->exercise('Ходьба для восстановления', 'Спокойная ходьба после интенсивного блока с контролем дыхания.', 'Заминка', 'Лёгкая', 'Без инвентаря', 7, ['заминка', 'восстановление', 'без инвентаря']),
            $this->exercise('Кошка-корова', 'Плавное чередование сгибания и разгибания позвоночника в упоре на четвереньках.', 'Мобильность', 'Лёгкая', 'Коврик', 5, ['мобильность', 'позвоночник', 'низкая нагрузка']),
            $this->exercise('Мёртвый жук', 'Попеременное опускание противоположных руки и ноги с прижатой поясницей.', 'Стабилизация', 'Средняя', 'Коврик', 8, ['кор', 'стабилизация', 'контроль']),
            $this->exercise('Птица-собака', 'Одновременное вытяжение противоположных руки и ноги без ротации таза.', 'Стабилизация', 'Средняя', 'Коврик', 8, ['кор', 'стабилизация', 'баланс']),
            $this->exercise('Боковая планка', 'Удержание боковой линии корпуса с опорой на предплечье.', 'Сила', 'Средняя', 'Коврик', 6, ['кор', 'силовая', 'статическое']),
            $this->exercise('Отжимания от стены', 'Контролируемые отжимания от стены для освоения жимового движения.', 'Сила', 'Лёгкая', 'Стена', 8, ['верх тела', 'силовая', 'низкая нагрузка']),
            $this->exercise('Тяга эспандера к поясу', 'Тяга эспандера с приведением лопаток и нейтральным положением спины.', 'Сила', 'Средняя', 'Эспандер', 10, ['спина', 'силовая', 'осанка']),
            $this->exercise('Наклон-тазовый шарнир', 'Отведение таза назад с нейтральной спиной для освоения паттерна наклона.', 'Сила', 'Лёгкая', 'Гимнастическая палка', 8, ['задняя цепь', 'техника', 'силовая']),
            $this->exercise('Подъёмы на носки', 'Подъёмы на носки стоя с контролируемым опусканием пяток.', 'Сила', 'Лёгкая', 'Без инвентаря', 7, ['голень', 'силовая', 'баланс']),
            $this->exercise('Зашагивания на платформу', 'Попеременные зашагивания на устойчивую невысокую платформу.', 'Сила', 'Средняя', 'Степ-платформа', 10, ['ноги', 'ягодицы', 'стабилизация']),
            $this->exercise('Боковые выпады', 'Перенос веса в сторону с отведением таза назад и контролем колена.', 'Сила', 'Средняя', 'Без инвентаря', 10, ['ноги', 'ягодицы', 'мобильность']),
            $this->exercise('Медвежья ходьба', 'Передвижение на ладонях и стопах с устойчивым положением корпуса.', 'Общая физическая подготовка', 'Высокая', 'Без инвентаря', 8, ['офп', 'кор', 'координация']),
            $this->exercise('Фермерская прогулка', 'Ходьба с симметричным грузом и стабильным положением корпуса.', 'Сила', 'Средняя', 'Гантели', 10, ['хват', 'кор', 'силовая']),
            $this->exercise('Прыжки через скакалку', 'Ритмичные невысокие прыжки с мягким приземлением.', 'Выносливость', 'Высокая', 'Скакалка', 8, ['кардио', 'прыжки', 'координация']),
            $this->exercise('Шаги jumping jack без прыжка', 'Попеременные шаги в сторону с подъёмом рук без ударной нагрузки.', 'Разминка', 'Лёгкая', 'Без инвентаря', 6, ['разминка', 'кардио', 'низкая нагрузка']),
            $this->exercise('Ловля мяча на реакцию', 'Ловля мяча после непредсказуемого сигнала или отскока.', 'Реакция', 'Средняя', 'Мяч', 8, ['реакция', 'координация', 'мяч']),
            $this->exercise('Боковые перемещения между конусами', 'Приставные шаги между отметками с сохранением низкой устойчивой позиции.', 'Координация', 'Средняя', 'Конусы', 10, ['координация', 'скорость', 'ноги']),
            $this->exercise('Темповый бег', 'Равномерный бег в контролируемом среднем темпе.', 'Выносливость', 'Средняя', 'Без инвентаря', 18, ['бег', 'кардио', 'выносливость']),
            $this->exercise('Поза ребёнка', 'Мягкое вытяжение спины и плеч с ровным дыханием.', 'Заминка', 'Лёгкая', 'Коврик', 5, ['заминка', 'мобильность', 'восстановление']),
            $this->exercise('Растяжка квадрицепса стоя', 'Мягкая растяжка передней поверхности бедра с устойчивой опорой.', 'Заминка', 'Лёгкая', 'Без инвентаря', 5, ['заминка', 'мобильность', 'ноги']),
            $this->exercise('Игра на удержание мяча', 'Командная игра с быстрыми передачами и открыванием в свободные зоны.', 'Игровой блок', 'Средняя', 'Мяч и конусы', 15, ['игровой блок', 'мяч', 'командная работа']),
        ];
    }

    /**
     * @return array<string, array{muscle_groups: list<string>, load_type: string, movement_pattern: string, contraindications?: string}>
     */
    private function metadata(): array
    {
        return [
            'Суставная разминка' => $this->metadataItem(['full_body'], 'warmup', 'stretch'),
            'Лёгкий бег' => $this->metadataItem(['legs', 'cardio'], 'warmup', 'run'),
            'Динамическая растяжка' => $this->metadataItem(['full_body'], 'mobility', 'stretch'),
            'Координационная лестница' => $this->metadataItem(['legs', 'core'], 'coordination', 'run'),
            'Змейка между конусами' => $this->metadataItem(['legs', 'core'], 'coordination', 'run'),
            'Баланс на одной ноге' => $this->metadataItem(['legs', 'core'], 'coordination', 'balance'),
            'Работа в парах на реакцию' => $this->metadataItem(['full_body'], 'coordination', 'balance'),
            'Приседания с собственным весом' => $this->metadataItem(['legs', 'glutes', 'core'], 'strength', 'squat', 'acute_knee_pain'),
            'Выпады назад' => $this->metadataItem(['legs', 'glutes', 'core'], 'strength', 'lunge', 'acute_knee_pain, poor_balance'),
            'Отжимания' => $this->metadataItem(['chest', 'triceps', 'shoulders', 'core'], 'strength', 'push', 'acute_shoulder_pain, acute_wrist_pain'),
            'Планка' => $this->metadataItem(['core', 'shoulders'], 'strength', 'core', 'acute_shoulder_pain'),
            'Ягодичный мост' => $this->metadataItem(['glutes', 'hamstrings', 'core'], 'strength', 'hinge'),
            'Прыжки на месте' => $this->metadataItem(['legs', 'calves'], 'cardio', 'jump', 'acute_knee_pain, acute_ankle_pain'),
            'Бег с ускорениями' => $this->metadataItem(['legs', 'cardio'], 'cardio', 'run', 'acute_lower_limb_pain'),
            'Челночный бег' => $this->metadataItem(['legs', 'cardio'], 'cardio', 'run', 'acute_knee_pain, poor_change_of_direction_control'),
            'Интервальный бег' => $this->metadataItem(['legs', 'cardio'], 'cardio', 'run'),
            'Передачи мяча в парах' => $this->metadataItem(['shoulders', 'arms', 'core'], 'coordination', 'push'),
            'Ведение мяча между конусами' => $this->metadataItem(['legs', 'core'], 'coordination', 'run'),
            'Эстафета с изменением направления' => $this->metadataItem(['full_body', 'cardio'], 'game', 'run'),
            'Игра «Зеркало»' => $this->metadataItem(['full_body'], 'game', 'balance'),
            'Круговая ОФП' => $this->metadataItem(['full_body'], 'strength', 'core'),
            'Мобилизация голеностопа' => $this->metadataItem(['calves', 'ankles'], 'mobility', 'stretch'),
            'Растяжка задней поверхности бедра' => $this->metadataItem(['hamstrings'], 'recovery', 'stretch'),
            'Дыхательное восстановление' => $this->metadataItem(['diaphragm'], 'recovery', 'breathing'),
            'Ходьба для восстановления' => $this->metadataItem(['legs', 'cardio'], 'recovery', 'run'),
            'Кошка-корова' => $this->metadataItem(['back', 'core'], 'mobility', 'stretch'),
            'Мёртвый жук' => $this->metadataItem(['core', 'hip_flexors'], 'strength', 'core'),
            'Птица-собака' => $this->metadataItem(['core', 'back', 'glutes'], 'strength', 'balance'),
            'Боковая планка' => $this->metadataItem(['obliques', 'shoulders', 'glutes'], 'strength', 'core', 'acute_shoulder_pain'),
            'Отжимания от стены' => $this->metadataItem(['chest', 'triceps', 'shoulders'], 'strength', 'push'),
            'Тяга эспандера к поясу' => $this->metadataItem(['back', 'biceps', 'rear_shoulders'], 'strength', 'pull'),
            'Наклон-тазовый шарнир' => $this->metadataItem(['hamstrings', 'glutes', 'back'], 'strength', 'hinge'),
            'Подъёмы на носки' => $this->metadataItem(['calves', 'ankles'], 'strength', 'balance'),
            'Зашагивания на платформу' => $this->metadataItem(['legs', 'glutes', 'core'], 'strength', 'lunge', 'acute_knee_pain, poor_balance'),
            'Боковые выпады' => $this->metadataItem(['legs', 'glutes', 'adductors'], 'strength', 'lunge', 'acute_knee_pain'),
            'Медвежья ходьба' => $this->metadataItem(['shoulders', 'core', 'legs'], 'strength', 'core', 'acute_wrist_pain, acute_shoulder_pain'),
            'Фермерская прогулка' => $this->metadataItem(['forearms', 'traps', 'core', 'legs'], 'strength', 'core', 'acute_back_pain'),
            'Прыжки через скакалку' => $this->metadataItem(['calves', 'legs', 'cardio'], 'cardio', 'jump', 'acute_knee_pain, acute_ankle_pain'),
            'Шаги jumping jack без прыжка' => $this->metadataItem(['legs', 'shoulders', 'cardio'], 'warmup', 'balance'),
            'Ловля мяча на реакцию' => $this->metadataItem(['shoulders', 'arms', 'core'], 'coordination', 'balance'),
            'Боковые перемещения между конусами' => $this->metadataItem(['legs', 'glutes', 'core'], 'coordination', 'run', 'acute_knee_pain'),
            'Темповый бег' => $this->metadataItem(['legs', 'cardio'], 'cardio', 'run'),
            'Поза ребёнка' => $this->metadataItem(['back', 'shoulders', 'hips'], 'recovery', 'stretch'),
            'Растяжка квадрицепса стоя' => $this->metadataItem(['quadriceps', 'hip_flexors'], 'recovery', 'stretch', 'poor_balance'),
            'Игра на удержание мяча' => $this->metadataItem(['full_body', 'cardio'], 'game', 'run'),
        ];
    }

    /**
     * @param  list<string>  $tags
     * @return array<string, mixed>
     */
    private function exercise(
        string $name,
        string $description,
        string $goal,
        string $difficulty,
        string $equipment,
        int $durationMinutes,
        array $tags,
    ): array {
        $metadata = $this->metadata()[$name];

        return [
            'name' => $name,
            'description' => $description,
            'goal' => $goal,
            'difficulty' => $difficulty,
            'equipment' => $equipment,
            'duration_minutes' => $durationMinutes,
            'muscle_groups' => $metadata['muscle_groups'],
            'load_type' => $metadata['load_type'],
            'movement_pattern' => $metadata['movement_pattern'],
            'contraindications' => $metadata['contraindications'] ?? null,
            'age_min' => 7,
            'age_max' => 70,
            'tags' => $tags,
        ];
    }

    /**
     * @param  list<string>  $muscleGroups
     * @return array{muscle_groups: list<string>, load_type: string, movement_pattern: string, contraindications?: string}
     */
    private function metadataItem(
        array $muscleGroups,
        string $loadType,
        string $movementPattern,
        ?string $contraindications = null,
    ): array {
        return array_filter([
            'muscle_groups' => $muscleGroups,
            'load_type' => $loadType,
            'movement_pattern' => $movementPattern,
            'contraindications' => $contraindications,
        ], fn (mixed $value): bool => $value !== null);
    }
}
