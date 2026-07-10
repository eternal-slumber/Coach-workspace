import { Link, useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import {
    store as storeTrainingPlan,
    update as updateTrainingPlan,
} from '@/actions/App/Http/Controllers/TrainingPlanController';
import InputError from '@/components/input-error';
import TrainingPlanBlockEditor from '@/components/training-plan-block-editor';
import type { TrainingPlanBlockInput } from '@/components/training-plan-block-editor';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/training-plans';
import type {
    ExercisePlanOption,
    ScheduledTrainingPlanOption,
    TrainingPlan,
    TrainingPlanStatus,
} from '@/types';

type TrainingPlanFormProps = {
    scheduledTrainings: ScheduledTrainingPlanOption[];
    exercises: ExercisePlanOption[];
    selectedScheduledTrainingId?: number | null;
    trainingPlan?: TrainingPlan;
};

type TrainingPlanFormData = {
    scheduled_training_id: number | null;
    title: string;
    goal: string;
    total_duration_minutes: number;
    status: TrainingPlanStatus;
    notes: string;
    blocks: TrainingPlanBlockInput[];
};

const selectClassName =
    'border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]';

const dateTimeFormatter = new Intl.DateTimeFormat('ru-RU', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

const emptyBlock = (): TrainingPlanBlockInput => ({
    name: '',
    duration_minutes: 10,
    notes: '',
    exercises: [],
});

function initialBlocks(trainingPlan?: TrainingPlan): TrainingPlanBlockInput[] {
    if (!trainingPlan) {
        return [{ ...emptyBlock(), name: 'Разминка' }];
    }

    return trainingPlan.blocks.map((block) => ({
        name: block.name,
        duration_minutes: block.duration_minutes,
        notes: block.notes ?? '',
        exercises: block.exercises.map((exercise) => ({
            exercise_id: exercise.exercise_id,
            name: exercise.name,
            description: exercise.description ?? '',
            duration_minutes: exercise.duration_minutes,
            sets: exercise.sets,
            repetitions: exercise.repetitions ?? '',
            rest_seconds: exercise.rest_seconds,
            notes: exercise.notes ?? '',
        })),
    }));
}

export default function TrainingPlanForm({
    scheduledTrainings,
    exercises,
    selectedScheduledTrainingId = null,
    trainingPlan,
}: TrainingPlanFormProps) {
    const form = useForm<TrainingPlanFormData>({
        scheduled_training_id:
            trainingPlan?.scheduled_training_id ?? selectedScheduledTrainingId,
        title: trainingPlan?.title ?? '',
        goal: trainingPlan?.goal ?? '',
        total_duration_minutes: trainingPlan?.total_duration_minutes ?? 60,
        status: trainingPlan?.status ?? 'draft',
        notes: trainingPlan?.notes ?? '',
        blocks: initialBlocks(trainingPlan),
    });
    const errors = form.errors as Record<string, string>;
    const blocksDuration = form.data.blocks.reduce(
        (total, block) => total + block.duration_minutes,
        0,
    );

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (trainingPlan) {
            form.patch(updateTrainingPlan.url(trainingPlan), {
                preserveScroll: true,
            });

            return;
        }

        form.post(storeTrainingPlan.url(), { preserveScroll: true });
    };

    const updateBlock = (blockIndex: number, block: TrainingPlanBlockInput) => {
        form.setData(
            'blocks',
            form.data.blocks.map((currentBlock, index) =>
                index === blockIndex ? block : currentBlock,
            ),
        );
    };

    return (
        <form onSubmit={submit} className="grid max-w-5xl gap-6">
            <Card>
                <CardContent className="grid gap-5 sm:grid-cols-2">
                    {trainingPlan ? (
                        <div className="grid gap-1 rounded-md border bg-muted/30 p-4 sm:col-span-2">
                            <p className="font-medium">
                                {trainingPlan.subject_name}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {dateTimeFormatter.format(
                                    new Date(
                                        trainingPlan.scheduled_training
                                            .starts_at,
                                    ),
                                )}{' '}
                                · {trainingPlan.scheduled_training.location}
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="scheduled_training_id">
                                Запланированная тренировка
                            </Label>
                            <select
                                id="scheduled_training_id"
                                className={selectClassName}
                                value={form.data.scheduled_training_id ?? ''}
                                onChange={(event) =>
                                    form.setData(
                                        'scheduled_training_id',
                                        event.target.value
                                            ? Number(event.target.value)
                                            : null,
                                    )
                                }
                            >
                                <option value="">Выберите тренировку</option>
                                {scheduledTrainings.map((scheduledTraining) => (
                                    <option
                                        key={scheduledTraining.id}
                                        value={scheduledTraining.id}
                                    >
                                        {scheduledTraining.subject_name} ·{' '}
                                        {dateTimeFormatter.format(
                                            new Date(
                                                scheduledTraining.starts_at,
                                            ),
                                        )}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                message={errors.scheduled_training_id}
                            />
                            {scheduledTrainings.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    Нет тренировок без плана. Сначала создайте
                                    событие расписания.
                                </p>
                            )}
                        </div>
                    )}

                    <div className="grid gap-2 sm:col-span-2">
                        <Label htmlFor="title">Название плана</Label>
                        <Input
                            id="title"
                            value={form.data.title}
                            onChange={(event) =>
                                form.setData('title', event.target.value)
                            }
                            placeholder="Тренировка на координацию и скорость"
                            autoFocus
                        />
                        <InputError message={errors.title} />
                    </div>

                    <div className="grid gap-2 sm:col-span-2">
                        <Label htmlFor="goal">Цель</Label>
                        <Textarea
                            id="goal"
                            value={form.data.goal}
                            onChange={(event) =>
                                form.setData('goal', event.target.value)
                            }
                        />
                        <InputError message={errors.goal} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="total_duration_minutes">
                            Общая длительность, мин.
                        </Label>
                        <Input
                            id="total_duration_minutes"
                            type="number"
                            min={1}
                            value={form.data.total_duration_minutes}
                            onChange={(event) =>
                                form.setData(
                                    'total_duration_minutes',
                                    Number(event.target.value),
                                )
                            }
                        />
                        <InputError message={errors.total_duration_minutes} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="status">Статус</Label>
                        <select
                            id="status"
                            className={selectClassName}
                            value={form.data.status}
                            disabled={trainingPlan?.status === 'completed'}
                            onChange={(event) =>
                                form.setData(
                                    'status',
                                    event.target.value as TrainingPlanStatus,
                                )
                            }
                        >
                            <option value="draft">Черновик</option>
                            <option value="approved">Утверждён</option>
                            {trainingPlan?.status === 'completed' && (
                                <option value="completed">Проведён</option>
                            )}
                        </select>
                        <InputError message={errors.status} />
                    </div>

                    <div className="grid gap-2 sm:col-span-2">
                        <Label htmlFor="notes">Общие заметки</Label>
                        <Textarea
                            id="notes"
                            value={form.data.notes}
                            onChange={(event) =>
                                form.setData('notes', event.target.value)
                            }
                        />
                        <InputError message={errors.notes} />
                    </div>
                </CardContent>
            </Card>

            <section className="grid gap-4" aria-labelledby="plan-blocks-title">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2
                            id="plan-blocks-title"
                            className="text-lg font-semibold"
                        >
                            Блоки тренировки
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Сумма блоков: {blocksDuration} мин.
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            form.setData('blocks', [
                                ...form.data.blocks,
                                emptyBlock(),
                            ])
                        }
                    >
                        <Plus />
                        Добавить блок
                    </Button>
                </div>

                <InputError message={errors.blocks} />

                {form.data.blocks.map((block, blockIndex) => (
                    <TrainingPlanBlockEditor
                        key={blockIndex}
                        block={block}
                        blockIndex={blockIndex}
                        exercises={exercises}
                        errors={errors}
                        onChange={(updatedBlock) =>
                            updateBlock(blockIndex, updatedBlock)
                        }
                        onRemove={() =>
                            form.setData(
                                'blocks',
                                form.data.blocks.filter(
                                    (_, index) => index !== blockIndex,
                                ),
                            )
                        }
                    />
                ))}
            </section>

            <div className="flex flex-wrap gap-3">
                <Button
                    type="submit"
                    disabled={
                        form.processing ||
                        (!trainingPlan && scheduledTrainings.length === 0)
                    }
                >
                    {form.processing ? 'Сохранение…' : 'Сохранить план'}
                </Button>
                <Button variant="outline" asChild>
                    <Link href={index()}>Отмена</Link>
                </Button>
            </div>
        </form>
    );
}
