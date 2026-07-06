import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { scheduledTrainingColorOptions } from '@/lib/scheduled-training-colors';
import { index } from '@/routes/scheduled-trainings';
import type {
    ScheduledTraining,
    ScheduledTrainingStatus,
    SelectionOption,
} from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

type ScheduledTrainingFormProps = {
    form: RouteFormDefinition<'post'>;
    submitLabel: string;
    trainees: SelectionOption[];
    trainingGroups: SelectionOption[];
    scheduledTraining?: ScheduledTraining;
};

const selectClassName =
    'border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50';

function pad(value: number): string {
    return value.toString().padStart(2, '0');
}

function toLocalDateTime(value: string | undefined, fallback: Date) {
    const date = value ? new Date(value) : fallback;

    return {
        date: `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`,
        time: `${pad(date.getHours())}:${pad(date.getMinutes())}`,
    };
}

export default function ScheduledTrainingForm({
    form,
    submitLabel,
    trainees,
    trainingGroups,
    scheduledTraining,
}: ScheduledTrainingFormProps) {
    const nextHour = new Date();
    nextHour.setMinutes(0, 0, 0);
    nextHour.setHours(nextHour.getHours() + 1);

    const hourAfter = new Date(nextHour);
    hourAfter.setHours(hourAfter.getHours() + 1);

    const startsAt = toLocalDateTime(scheduledTraining?.starts_at, nextHour);
    const endsAt = toLocalDateTime(scheduledTraining?.ends_at, hourAfter);

    return (
        <Form
            {...form}
            transform={(data) => ({
                ...data,
                trainee_id: data.trainee_id ? Number(data.trainee_id) : null,
                training_group_id: data.training_group_id
                    ? Number(data.training_group_id)
                    : null,
                starts_at: new Date(
                    `${String(data.date)}T${String(data.start_time)}`,
                ).toISOString(),
                ends_at: new Date(
                    `${String(data.date)}T${String(data.end_time)}`,
                ).toISOString(),
            })}
            className="grid gap-6"
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="date">Дата</Label>
                            <Input
                                id="date"
                                name="date"
                                type="date"
                                defaultValue={startsAt.date}
                                required
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="start_time">Время начала</Label>
                            <Input
                                id="start_time"
                                name="start_time"
                                type="time"
                                defaultValue={startsAt.time}
                                required
                            />
                            <InputError message={errors.starts_at} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="end_time">Время окончания</Label>
                            <Input
                                id="end_time"
                                name="end_time"
                                type="time"
                                defaultValue={endsAt.time}
                                required
                            />
                            <InputError message={errors.ends_at} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="trainee_id">Клиент</Label>
                            <select
                                id="trainee_id"
                                name="trainee_id"
                                className={selectClassName}
                                defaultValue={
                                    scheduledTraining?.trainee_id ?? ''
                                }
                            >
                                <option value="">Не выбран</option>
                                {trainees.map((trainee) => (
                                    <option key={trainee.id} value={trainee.id}>
                                        {trainee.name}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.trainee_id} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="training_group_id">Группа</Label>
                            <select
                                id="training_group_id"
                                name="training_group_id"
                                className={selectClassName}
                                defaultValue={
                                    scheduledTraining?.training_group_id ?? ''
                                }
                            >
                                <option value="">Не выбрана</option>
                                {trainingGroups.map((trainingGroup) => (
                                    <option
                                        key={trainingGroup.id}
                                        value={trainingGroup.id}
                                    >
                                        {trainingGroup.name}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.training_group_id} />
                        </div>

                        <p className="text-sm text-muted-foreground sm:col-span-2">
                            Выберите либо клиента, либо группу.
                        </p>

                        <div className="grid gap-2">
                            <Label htmlFor="location">Локация</Label>
                            <Input
                                id="location"
                                name="location"
                                defaultValue={scheduledTraining?.location}
                                required
                            />
                            <InputError message={errors.location} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="status">Статус</Label>
                            <select
                                id="status"
                                name="status"
                                className={selectClassName}
                                defaultValue={
                                    scheduledTraining?.status ?? 'planned'
                                }
                            >
                                {(
                                    [
                                        'planned',
                                        'completed',
                                        'cancelled',
                                    ] satisfies ScheduledTrainingStatus[]
                                ).map((status) => (
                                    <option key={status} value={status}>
                                        {status}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.status} />
                        </div>

                        <fieldset className="grid gap-2 sm:col-span-2">
                            <legend className="text-sm leading-none font-medium">
                                Цвет в календаре
                            </legend>
                            <div className="flex flex-wrap gap-2">
                                {scheduledTrainingColorOptions.map(
                                    (colorOption) => (
                                        <label
                                            key={colorOption.value}
                                            className="cursor-pointer"
                                        >
                                            <input
                                                type="radio"
                                                name="color"
                                                value={colorOption.value}
                                                defaultChecked={
                                                    (scheduledTraining?.color ??
                                                        'blue') ===
                                                    colorOption.value
                                                }
                                                className="peer sr-only"
                                                required
                                            />
                                            <span className="flex h-9 items-center gap-2 rounded-md border px-3 text-sm shadow-xs transition-colors peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/20 peer-focus-visible:ring-2 peer-focus-visible:ring-ring hover:bg-accent">
                                                <span
                                                    className={`size-3 rounded-full ${colorOption.swatchClassName}`}
                                                />
                                                {colorOption.label}
                                            </span>
                                        </label>
                                    ),
                                )}
                            </div>
                            <InputError message={errors.color} />
                        </fieldset>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="notes">Заметка</Label>
                            <Textarea
                                id="notes"
                                name="notes"
                                defaultValue={scheduledTraining?.notes ?? ''}
                                placeholder="Дополнительная информация о тренировке"
                            />
                            <InputError message={errors.notes} />
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
