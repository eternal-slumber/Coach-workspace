import { Plus, Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { ExercisePlanOption } from '@/types';

export type TrainingPlanExerciseInput = {
    exercise_id: number | null;
    name: string;
    description: string;
    duration_minutes: number | null;
    sets: number | null;
    repetitions: string;
    rest_seconds: number | null;
    notes: string;
};

export type TrainingPlanBlockInput = {
    name: string;
    duration_minutes: number;
    notes: string;
    exercises: TrainingPlanExerciseInput[];
};

type TrainingPlanBlockEditorProps = {
    block: TrainingPlanBlockInput;
    blockIndex: number;
    exercises: ExercisePlanOption[];
    errors: Record<string, string>;
    onChange: (block: TrainingPlanBlockInput) => void;
    onRemove: () => void;
};

const selectClassName =
    'border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]';

const emptyExercise = (): TrainingPlanExerciseInput => ({
    exercise_id: null,
    name: '',
    description: '',
    duration_minutes: null,
    sets: null,
    repetitions: '',
    rest_seconds: null,
    notes: '',
});

function optionalNumber(value: string): number | null {
    return value === '' ? null : Number(value);
}

export default function TrainingPlanBlockEditor({
    block,
    blockIndex,
    exercises,
    errors,
    onChange,
    onRemove,
}: TrainingPlanBlockEditorProps) {
    const updateExercise = (
        exerciseIndex: number,
        exercise: TrainingPlanExerciseInput,
    ) => {
        onChange({
            ...block,
            exercises: block.exercises.map((currentExercise, index) =>
                index === exerciseIndex ? exercise : currentExercise,
            ),
        });
    };

    const selectExercise = (exerciseIndex: number, value: string) => {
        const currentExercise = block.exercises[exerciseIndex];
        const selectedExercise = exercises.find(
            (exercise) => exercise.id === Number(value),
        );

        if (!selectedExercise) {
            updateExercise(exerciseIndex, {
                ...currentExercise,
                exercise_id: null,
            });

            return;
        }

        updateExercise(exerciseIndex, {
            ...currentExercise,
            exercise_id: selectedExercise.id,
            name: selectedExercise.name,
            description: selectedExercise.description,
            duration_minutes: selectedExercise.duration_minutes,
        });
    };

    const removeExercise = (exerciseIndex: number) => {
        onChange({
            ...block,
            exercises: block.exercises.filter(
                (_, index) => index !== exerciseIndex,
            ),
        });
    };

    return (
        <Card>
            <CardHeader className="flex-row items-center justify-between gap-3">
                <CardTitle>Блок {blockIndex + 1}</CardTitle>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={onRemove}
                >
                    <Trash2 />
                    Удалить блок
                </Button>
            </CardHeader>
            <CardContent className="grid gap-5">
                <div className="grid gap-4 sm:grid-cols-[1fr_12rem]">
                    <div className="grid gap-2">
                        <Label htmlFor={`blocks-${blockIndex}-name`}>
                            Название блока
                        </Label>
                        <Input
                            id={`blocks-${blockIndex}-name`}
                            value={block.name}
                            onChange={(event) =>
                                onChange({ ...block, name: event.target.value })
                            }
                            placeholder="Разминка"
                        />
                        <InputError
                            message={errors[`blocks.${blockIndex}.name`]}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor={`blocks-${blockIndex}-duration`}>
                            Длительность, мин.
                        </Label>
                        <Input
                            id={`blocks-${blockIndex}-duration`}
                            type="number"
                            min={1}
                            value={block.duration_minutes}
                            onChange={(event) =>
                                onChange({
                                    ...block,
                                    duration_minutes: Number(
                                        event.target.value,
                                    ),
                                })
                            }
                        />
                        <InputError
                            message={
                                errors[`blocks.${blockIndex}.duration_minutes`]
                            }
                        />
                    </div>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor={`blocks-${blockIndex}-notes`}>
                        Заметка к блоку
                    </Label>
                    <Textarea
                        id={`blocks-${blockIndex}-notes`}
                        value={block.notes}
                        onChange={(event) =>
                            onChange({ ...block, notes: event.target.value })
                        }
                    />
                    <InputError
                        message={errors[`blocks.${blockIndex}.notes`]}
                    />
                </div>

                <div className="grid gap-3 border-t pt-5">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h3 className="font-medium">Упражнения</h3>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                onChange({
                                    ...block,
                                    exercises: [
                                        ...block.exercises,
                                        emptyExercise(),
                                    ],
                                })
                            }
                        >
                            <Plus />
                            Добавить упражнение
                        </Button>
                    </div>

                    {block.exercises.length === 0 ? (
                        <p className="rounded-md border border-dashed p-4 text-sm text-muted-foreground">
                            В этом блоке пока нет упражнений.
                        </p>
                    ) : (
                        block.exercises.map((exercise, exerciseIndex) => (
                            <div
                                key={exerciseIndex}
                                className="grid gap-4 rounded-lg border bg-muted/20 p-4"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <p className="text-sm font-medium">
                                        Упражнение {exerciseIndex + 1}
                                    </p>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        aria-label="Удалить упражнение"
                                        onClick={() =>
                                            removeExercise(exerciseIndex)
                                        }
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>

                                <div className="grid gap-2">
                                    <Label
                                        htmlFor={`blocks-${blockIndex}-exercise-${exerciseIndex}-library`}
                                    >
                                        Выбрать из базы
                                    </Label>
                                    <select
                                        id={`blocks-${blockIndex}-exercise-${exerciseIndex}-library`}
                                        className={selectClassName}
                                        value={exercise.exercise_id ?? ''}
                                        onChange={(event) =>
                                            selectExercise(
                                                exerciseIndex,
                                                event.target.value,
                                            )
                                        }
                                    >
                                        <option value="">
                                            Ввести упражнение вручную
                                        </option>
                                        {exercises.map((exerciseOption) => (
                                            <option
                                                key={exerciseOption.id}
                                                value={exerciseOption.id}
                                            >
                                                {exerciseOption.name}
                                                {exerciseOption.is_system
                                                    ? ' · системное'
                                                    : ' · моё'}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={
                                            errors[
                                                `blocks.${blockIndex}.exercises.${exerciseIndex}.exercise_id`
                                            ]
                                        }
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label
                                        htmlFor={`blocks-${blockIndex}-exercise-${exerciseIndex}-name`}
                                    >
                                        Название
                                    </Label>
                                    <Input
                                        id={`blocks-${blockIndex}-exercise-${exerciseIndex}-name`}
                                        value={exercise.name}
                                        onChange={(event) =>
                                            updateExercise(exerciseIndex, {
                                                ...exercise,
                                                name: event.target.value,
                                            })
                                        }
                                    />
                                    <InputError
                                        message={
                                            errors[
                                                `blocks.${blockIndex}.exercises.${exerciseIndex}.name`
                                            ]
                                        }
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label
                                        htmlFor={`blocks-${blockIndex}-exercise-${exerciseIndex}-description`}
                                    >
                                        Описание
                                    </Label>
                                    <Textarea
                                        id={`blocks-${blockIndex}-exercise-${exerciseIndex}-description`}
                                        value={exercise.description}
                                        onChange={(event) =>
                                            updateExercise(exerciseIndex, {
                                                ...exercise,
                                                description: event.target.value,
                                            })
                                        }
                                    />
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div className="grid gap-2">
                                        <Label>Минуты</Label>
                                        <Input
                                            type="number"
                                            min={1}
                                            value={
                                                exercise.duration_minutes ?? ''
                                            }
                                            onChange={(event) =>
                                                updateExercise(exerciseIndex, {
                                                    ...exercise,
                                                    duration_minutes:
                                                        optionalNumber(
                                                            event.target.value,
                                                        ),
                                                })
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Подходы</Label>
                                        <Input
                                            type="number"
                                            min={1}
                                            value={exercise.sets ?? ''}
                                            onChange={(event) =>
                                                updateExercise(exerciseIndex, {
                                                    ...exercise,
                                                    sets: optionalNumber(
                                                        event.target.value,
                                                    ),
                                                })
                                            }
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Повторы</Label>
                                        <Input
                                            value={exercise.repetitions}
                                            onChange={(event) =>
                                                updateExercise(exerciseIndex, {
                                                    ...exercise,
                                                    repetitions:
                                                        event.target.value,
                                                })
                                            }
                                            placeholder="10 или 30 сек."
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Отдых, сек.</Label>
                                        <Input
                                            type="number"
                                            min={0}
                                            value={exercise.rest_seconds ?? ''}
                                            onChange={(event) =>
                                                updateExercise(exerciseIndex, {
                                                    ...exercise,
                                                    rest_seconds:
                                                        optionalNumber(
                                                            event.target.value,
                                                        ),
                                                })
                                            }
                                        />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label>Заметка тренера</Label>
                                    <Textarea
                                        value={exercise.notes}
                                        onChange={(event) =>
                                            updateExercise(exerciseIndex, {
                                                ...exercise,
                                                notes: event.target.value,
                                            })
                                        }
                                    />
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
