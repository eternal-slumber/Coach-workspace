import { Form } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useState } from 'react';
import {
    store,
    update,
} from '@/actions/App/Http/Controllers/TrainingNoteController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type {
    TrainingNote,
    TrainingNoteIntensity,
    TrainingNoteResult,
    TrainingPlan,
} from '@/types';

const intensityLabels: Record<TrainingNoteIntensity, string> = {
    low: 'Низкая',
    medium: 'Средняя',
    high: 'Высокая',
};

const resultLabels: Record<TrainingNoteResult, string> = {
    bad: 'Плохо',
    normal: 'Нормально',
    good: 'Хорошо',
};

const selectClassName =
    'border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]';

function TrainingNoteForm({
    trainingPlan,
    trainingNote,
    onCancel,
}: {
    trainingPlan: TrainingPlan;
    trainingNote: TrainingNote | null;
    onCancel?: () => void;
}) {
    const form = trainingNote
        ? update.form.patch(trainingNote)
        : store.form(trainingPlan);

    return (
        <Form {...form} onSuccess={onCancel} className="grid gap-5">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="training-note-intensity">
                                Нагрузка
                            </Label>
                            <select
                                id="training-note-intensity"
                                name="intensity"
                                className={selectClassName}
                                defaultValue={
                                    trainingNote?.intensity ?? 'medium'
                                }
                            >
                                <option value="low">Низкая</option>
                                <option value="medium">Средняя</option>
                                <option value="high">Высокая</option>
                            </select>
                            <InputError message={errors.intensity} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="training-note-result">
                                Результат
                            </Label>
                            <select
                                id="training-note-result"
                                name="result"
                                className={selectClassName}
                                defaultValue={trainingNote?.result ?? 'normal'}
                            >
                                <option value="bad">Плохо</option>
                                <option value="normal">Нормально</option>
                                <option value="good">Хорошо</option>
                            </select>
                            <InputError message={errors.result} />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="training-note-tags">Теги</Label>
                        <Textarea
                            id="training-note-tags"
                            name="tags"
                            defaultValue={trainingNote?.tags.join(', ') ?? ''}
                            placeholder="устали, повторить технику, снизить нагрузку"
                            className="min-h-20"
                        />
                        <p className="text-xs text-muted-foreground">
                            Разделяйте теги запятыми.
                        </p>
                        <InputError message={errors.tags} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="training-note-note">Комментарий</Label>
                        <Textarea
                            id="training-note-note"
                            name="note"
                            defaultValue={trainingNote?.note ?? ''}
                            placeholder="Как прошло занятие и что учесть в следующий раз"
                            className="min-h-32"
                            required
                        />
                        <InputError message={errors.note} />
                        <InputError message={errors.training_plan} />
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing
                                ? 'Сохранение…'
                                : trainingNote
                                  ? 'Сохранить изменения'
                                  : 'Добавить заметку'}
                        </Button>
                        {onCancel && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={onCancel}
                            >
                                Отмена
                            </Button>
                        )}
                    </div>
                </>
            )}
        </Form>
    );
}

export default function TrainingNoteSection({
    trainingPlan,
}: {
    trainingPlan: TrainingPlan;
}) {
    const [isEditing, setIsEditing] = useState(false);
    const trainingNote = trainingPlan.training_note;

    if (trainingPlan.status !== 'completed') {
        return null;
    }

    return (
        <Card className="max-w-5xl">
            <CardHeader className="flex-row items-center justify-between gap-3">
                <div className="grid gap-1">
                    <CardTitle>Заметка после тренировки</CardTitle>
                    <p className="text-sm text-muted-foreground">
                        Итоги занятия и контекст для следующей тренировки
                    </p>
                </div>
                {trainingNote && !isEditing && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => setIsEditing(true)}
                    >
                        <Pencil />
                        Редактировать заметку
                    </Button>
                )}
            </CardHeader>
            <CardContent>
                {trainingNote && !isEditing ? (
                    <div className="grid gap-5">
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-1">
                                <dt className="text-sm text-muted-foreground">
                                    Нагрузка
                                </dt>
                                <dd>
                                    {intensityLabels[trainingNote.intensity]}
                                </dd>
                            </div>
                            <div className="grid gap-1">
                                <dt className="text-sm text-muted-foreground">
                                    Результат
                                </dt>
                                <dd>{resultLabels[trainingNote.result]}</dd>
                            </div>
                            <div className="grid gap-1 sm:col-span-2">
                                <dt className="text-sm text-muted-foreground">
                                    Комментарий
                                </dt>
                                <dd className="whitespace-pre-wrap">
                                    {trainingNote.note}
                                </dd>
                            </div>
                        </dl>
                        {trainingNote.tags.length > 0 && (
                            <div className="flex flex-wrap gap-1.5">
                                {trainingNote.tags.map((tag) => (
                                    <Badge key={tag} variant="outline">
                                        {tag}
                                    </Badge>
                                ))}
                            </div>
                        )}
                    </div>
                ) : (
                    <TrainingNoteForm
                        trainingPlan={trainingPlan}
                        trainingNote={trainingNote}
                        onCancel={
                            trainingNote ? () => setIsEditing(false) : undefined
                        }
                    />
                )}
            </CardContent>
        </Card>
    );
}
