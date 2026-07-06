import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/training-groups';
import type { TrainingGroup } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

type TrainingGroupFormProps = {
    form: RouteFormDefinition<'post'>;
    submitLabel: string;
    trainingGroup?: TrainingGroup;
};

export default function TrainingGroupForm({
    form,
    submitLabel,
    trainingGroup,
}: TrainingGroupFormProps) {
    return (
        <Form {...form} className="grid gap-6">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="name">Название группы</Label>
                            <Input
                                id="name"
                                name="name"
                                defaultValue={trainingGroup?.name}
                                required
                                autoFocus
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="sport_type">Вид спорта</Label>
                            <Input
                                id="sport_type"
                                name="sport_type"
                                defaultValue={trainingGroup?.sport_type}
                                required
                            />
                            <InputError message={errors.sport_type} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="age_range">
                                Возрастной диапазон
                            </Label>
                            <Input
                                id="age_range"
                                name="age_range"
                                defaultValue={trainingGroup?.age_range}
                                placeholder="Например, 10–12 лет"
                                required
                            />
                            <InputError message={errors.age_range} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="level">Уровень подготовки</Label>
                            <Input
                                id="level"
                                name="level"
                                defaultValue={trainingGroup?.level}
                                required
                            />
                            <InputError message={errors.level} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="goal">Цель группы</Label>
                            <Textarea
                                id="goal"
                                name="goal"
                                defaultValue={trainingGroup?.goal}
                                required
                            />
                            <InputError message={errors.goal} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="restrictions">Ограничения</Label>
                            <Textarea
                                id="restrictions"
                                name="restrictions"
                                defaultValue={trainingGroup?.restrictions ?? ''}
                            />
                            <InputError message={errors.restrictions} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="notes">Заметки</Label>
                            <Textarea
                                id="notes"
                                name="notes"
                                defaultValue={trainingGroup?.notes ?? ''}
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
