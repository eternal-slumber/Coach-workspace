import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { create, index, show } from '@/routes/exercises';
import type { ExerciseListItem } from '@/types';

type ExercisesIndexProps = {
    exercises: ExerciseListItem[];
};

function ExerciseCard({ exercise }: { exercise: ExerciseListItem }) {
    return (
        <Card className="gap-3">
            <CardContent className="grid gap-4">
                <div className="flex items-start justify-between gap-3">
                    <div className="grid gap-1">
                        <Link
                            href={show(exercise)}
                            className="font-semibold hover:underline"
                        >
                            {exercise.name}
                        </Link>
                        <p className="text-sm text-muted-foreground">
                            {exercise.goal} · {exercise.difficulty}
                        </p>
                    </div>
                    <Badge
                        variant={exercise.is_system ? 'secondary' : 'outline'}
                    >
                        {exercise.is_system ? 'Системное' : 'Моё'}
                    </Badge>
                </div>

                <div className="grid gap-1 text-sm">
                    <p>{exercise.equipment || 'Инвентарь не требуется'}</p>
                    {exercise.duration_minutes && (
                        <p className="text-muted-foreground">
                            Примерно {exercise.duration_minutes} мин.
                        </p>
                    )}
                    {(exercise.load_type || exercise.movement_pattern) && (
                        <p className="text-muted-foreground">
                            {[exercise.load_type, exercise.movement_pattern]
                                .filter(Boolean)
                                .join(' · ')}
                        </p>
                    )}
                </div>

                {exercise.tags.length > 0 && (
                    <div className="flex flex-wrap gap-1.5">
                        {exercise.tags.map((tag) => (
                            <Badge key={tag} variant="outline">
                                {tag}
                            </Badge>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function ExerciseSection({
    title,
    exercises,
}: {
    title: string;
    exercises: ExerciseListItem[];
}) {
    if (exercises.length === 0) {
        return null;
    }

    return (
        <section className="grid gap-3" aria-label={title}>
            <h2 className="text-lg font-semibold">{title}</h2>
            <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                {exercises.map((exercise) => (
                    <ExerciseCard key={exercise.id} exercise={exercise} />
                ))}
            </div>
        </section>
    );
}

export default function ExercisesIndex({ exercises }: ExercisesIndexProps) {
    const systemExercises = exercises.filter((exercise) => exercise.is_system);
    const personalExercises = exercises.filter(
        (exercise) => !exercise.is_system,
    );

    return (
        <>
            <Head title="Упражнения" />

            <main className="flex flex-1 flex-col gap-7 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="База упражнений"
                        description="Системные упражнения и ваша личная библиотека"
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Добавить упражнение
                        </Link>
                    </Button>
                </div>

                {exercises.length === 0 ? (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            Упражнений пока нет
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <ExerciseSection
                            title="Мои упражнения"
                            exercises={personalExercises}
                        />
                        <ExerciseSection
                            title="Системные упражнения"
                            exercises={systemExercises}
                        />
                    </>
                )}
            </main>
        </>
    );
}

ExercisesIndex.layout = {
    breadcrumbs: [{ title: 'Упражнения', href: index() }],
};
