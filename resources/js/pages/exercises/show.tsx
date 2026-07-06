import { Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
import ResourceDeleteDialog from '@/components/resource-delete-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { edit, index } from '@/routes/exercises';
import type { Exercise } from '@/types';

type ExercisesShowProps = {
    exercise: Exercise;
    canManage: boolean;
};

function Detail({ label, value }: { label: string; value: string | null }) {
    return (
        <div className="grid gap-1">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd className="whitespace-pre-wrap">{value || 'Не указано'}</dd>
        </div>
    );
}

function ageRange(exercise: Exercise): string | null {
    if (exercise.age_min && exercise.age_max) {
        return `${exercise.age_min}–${exercise.age_max} лет`;
    }

    if (exercise.age_min) {
        return `От ${exercise.age_min} лет`;
    }

    if (exercise.age_max) {
        return `До ${exercise.age_max} лет`;
    }

    return null;
}

export default function ExercisesShow({
    exercise,
    canManage,
}: ExercisesShowProps) {
    return (
        <>
            <Head title={exercise.name} />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="grid gap-2">
                        <div className="flex flex-wrap items-center gap-2">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {exercise.name}
                            </h1>
                            <Badge
                                variant={
                                    exercise.is_system ? 'secondary' : 'outline'
                                }
                            >
                                {exercise.is_system ? 'Системное' : 'Моё'}
                            </Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            Карточка упражнения
                        </p>
                    </div>

                    {canManage && (
                        <div className="flex flex-wrap gap-3">
                            <Button variant="outline" asChild>
                                <Link href={edit(exercise)}>
                                    <Pencil />
                                    Редактировать
                                </Link>
                            </Button>
                            <ResourceDeleteDialog
                                form={ExerciseController.destroy.form(exercise)}
                                resourceName={exercise.name}
                            />
                        </div>
                    )}
                </div>

                <Card className="max-w-3xl">
                    <CardContent>
                        <dl className="grid gap-6 sm:grid-cols-2">
                            <div className="sm:col-span-2">
                                <Detail
                                    label="Описание"
                                    value={exercise.description}
                                />
                            </div>
                            <Detail label="Цель" value={exercise.goal} />
                            <Detail
                                label="Сложность"
                                value={exercise.difficulty}
                            />
                            <Detail
                                label="Инвентарь"
                                value={exercise.equipment}
                            />
                            <Detail
                                label="Длительность"
                                value={
                                    exercise.duration_minutes
                                        ? `${exercise.duration_minutes} мин.`
                                        : null
                                }
                            />
                            <Detail
                                label="Возраст"
                                value={ageRange(exercise)}
                            />
                            <div className="sm:col-span-2">
                                <Detail
                                    label="Ограничения и противопоказания"
                                    value={exercise.contraindications}
                                />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <dt className="text-sm text-muted-foreground">
                                    Теги
                                </dt>
                                <dd className="flex flex-wrap gap-1.5">
                                    {exercise.tags.length > 0 ? (
                                        exercise.tags.map((tag) => (
                                            <Badge key={tag} variant="outline">
                                                {tag}
                                            </Badge>
                                        ))
                                    ) : (
                                        <span>Не указано</span>
                                    )}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

ExercisesShow.layout = {
    breadcrumbs: [{ title: 'Упражнения', href: index() }],
};
