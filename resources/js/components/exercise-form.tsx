import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/exercises';
import type { Exercise } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

type ExerciseFormProps = {
    form: RouteFormDefinition<'post'>;
    submitLabel: string;
    exercise?: Exercise;
};

const selectClassName =
    'border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50';

export default function ExerciseForm({
    form,
    submitLabel,
    exercise,
}: ExerciseFormProps) {
    return (
        <Form {...form} className="grid gap-6">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="name">Название</Label>
                            <Input
                                id="name"
                                name="name"
                                defaultValue={exercise?.name}
                                required
                                autoFocus
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="description">Описание</Label>
                            <Textarea
                                id="description"
                                name="description"
                                defaultValue={exercise?.description}
                                required
                                className="min-h-28"
                            />
                            <InputError message={errors.description} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="goal">Цель упражнения</Label>
                            <Input
                                id="goal"
                                name="goal"
                                defaultValue={exercise?.goal}
                                required
                            />
                            <InputError message={errors.goal} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="difficulty">Сложность</Label>
                            <select
                                id="difficulty"
                                name="difficulty"
                                className={selectClassName}
                                defaultValue={exercise?.difficulty ?? 'Лёгкая'}
                                required
                            >
                                <option value="Лёгкая">Лёгкая</option>
                                <option value="Средняя">Средняя</option>
                                <option value="Высокая">Высокая</option>
                            </select>
                            <InputError message={errors.difficulty} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="equipment">Инвентарь</Label>
                            <Input
                                id="equipment"
                                name="equipment"
                                defaultValue={exercise?.equipment ?? ''}
                                placeholder="Например, мяч или без инвентаря"
                            />
                            <InputError message={errors.equipment} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="duration_minutes">
                                Длительность, минут
                            </Label>
                            <Input
                                id="duration_minutes"
                                name="duration_minutes"
                                type="number"
                                min={1}
                                max={480}
                                defaultValue={exercise?.duration_minutes ?? ''}
                            />
                            <InputError message={errors.duration_minutes} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="muscle_groups">Группы мышц</Label>
                            <Input
                                id="muscle_groups"
                                name="muscle_groups"
                                defaultValue={
                                    exercise?.muscle_groups.join(', ') ?? ''
                                }
                                placeholder="legs, glutes, core"
                            />
                            <p className="text-xs text-muted-foreground">
                                Разделяйте группы мышц запятыми.
                            </p>
                            <InputError message={errors.muscle_groups} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="load_type">Тип нагрузки</Label>
                            <select
                                id="load_type"
                                name="load_type"
                                className={selectClassName}
                                defaultValue={exercise?.load_type ?? ''}
                            >
                                <option value="">Не указан</option>
                                <option value="warmup">Разминка</option>
                                <option value="strength">Сила</option>
                                <option value="mobility">Мобильность</option>
                                <option value="coordination">
                                    Координация
                                </option>
                                <option value="cardio">Кардио</option>
                                <option value="recovery">Восстановление</option>
                                <option value="game">Игровая нагрузка</option>
                            </select>
                            <InputError message={errors.load_type} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="movement_pattern">
                                Двигательный паттерн
                            </Label>
                            <select
                                id="movement_pattern"
                                name="movement_pattern"
                                className={selectClassName}
                                defaultValue={exercise?.movement_pattern ?? ''}
                            >
                                <option value="">Не указан</option>
                                <option value="squat">Приседание</option>
                                <option value="lunge">Выпад</option>
                                <option value="hinge">Тазовый наклон</option>
                                <option value="push">Жим</option>
                                <option value="pull">Тяга</option>
                                <option value="core">
                                    Стабилизация корпуса
                                </option>
                                <option value="balance">Баланс</option>
                                <option value="run">Бег</option>
                                <option value="jump">Прыжок</option>
                                <option value="stretch">Растяжка</option>
                                <option value="breathing">Дыхание</option>
                            </select>
                            <InputError message={errors.movement_pattern} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="age_min">Минимальный возраст</Label>
                            <Input
                                id="age_min"
                                name="age_min"
                                type="number"
                                min={1}
                                max={120}
                                defaultValue={exercise?.age_min ?? ''}
                            />
                            <InputError message={errors.age_min} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="age_max">
                                Максимальный возраст
                            </Label>
                            <Input
                                id="age_max"
                                name="age_max"
                                type="number"
                                min={1}
                                max={120}
                                defaultValue={exercise?.age_max ?? ''}
                            />
                            <InputError message={errors.age_max} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="tags">Теги</Label>
                            <Input
                                id="tags"
                                name="tags"
                                defaultValue={exercise?.tags.join(', ') ?? ''}
                                placeholder="разминка, координация, дети"
                            />
                            <p className="text-xs text-muted-foreground">
                                Разделяйте теги запятыми.
                            </p>
                            <InputError message={errors.tags} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="contraindications">
                                Ограничения и противопоказания
                            </Label>
                            <Textarea
                                id="contraindications"
                                name="contraindications"
                                defaultValue={exercise?.contraindications ?? ''}
                            />
                            <InputError message={errors.contraindications} />
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Сохранение…' : submitLabel}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={index()}>Отмена</Link>
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
