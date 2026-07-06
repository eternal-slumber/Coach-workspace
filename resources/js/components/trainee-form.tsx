import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/trainees';
import type { Trainee } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

type TraineeFormProps = {
    form: RouteFormDefinition<'post'>;
    submitLabel: string;
    trainee?: Trainee;
};

export default function TraineeForm({
    form,
    submitLabel,
    trainee,
}: TraineeFormProps) {
    return (
        <Form {...form} className="grid gap-6">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="name">Имя</Label>
                            <Input
                                id="name"
                                name="name"
                                defaultValue={trainee?.name}
                                required
                                autoFocus
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="age">Возраст</Label>
                            <Input
                                id="age"
                                name="age"
                                type="number"
                                min={1}
                                max={120}
                                defaultValue={trainee?.age ?? ''}
                            />
                            <InputError message={errors.age} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="level">Уровень подготовки</Label>
                            <Input
                                id="level"
                                name="level"
                                defaultValue={trainee?.level}
                                required
                            />
                            <InputError message={errors.level} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="goal">Цель</Label>
                            <Textarea
                                id="goal"
                                name="goal"
                                defaultValue={trainee?.goal}
                                required
                            />
                            <InputError message={errors.goal} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="restrictions">Ограничения</Label>
                            <Textarea
                                id="restrictions"
                                name="restrictions"
                                defaultValue={trainee?.restrictions ?? ''}
                            />
                            <InputError message={errors.restrictions} />
                        </div>

                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="notes">Заметки</Label>
                            <Textarea
                                id="notes"
                                name="notes"
                                defaultValue={trainee?.notes ?? ''}
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
