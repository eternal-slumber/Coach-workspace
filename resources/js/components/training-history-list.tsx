import { Link } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';
import TrainingPlanStatusBadge from '@/components/training-plan-status-badge';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { show } from '@/routes/training-plans';
import type { TrainingHistoryItem } from '@/types';

const intensityLabels = {
    low: 'низкая',
    medium: 'средняя',
    high: 'высокая',
} as const;

const resultLabels = {
    bad: 'плохо',
    normal: 'нормально',
    good: 'хорошо',
} as const;

const dateFormatter = new Intl.DateTimeFormat('ru-RU', {
    dateStyle: 'long',
});

export default function TrainingHistoryList({
    trainingHistory,
}: {
    trainingHistory: TrainingHistoryItem[];
}) {
    return (
        <Card className="max-w-3xl">
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <CalendarDays className="size-5" />
                    История тренировок
                </CardTitle>
            </CardHeader>
            <CardContent>
                {trainingHistory.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Проведённых тренировок пока нет
                    </p>
                ) : (
                    <div className="grid gap-3">
                        {trainingHistory.map((trainingPlan) => (
                            <Link
                                key={trainingPlan.id}
                                href={show(trainingPlan)}
                                className="grid gap-2 rounded-lg border p-4 transition-colors hover:bg-accent sm:grid-cols-[1fr_auto] sm:items-center"
                            >
                                <div className="grid gap-1">
                                    <p className="font-medium">
                                        {trainingPlan.title}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {dateFormatter.format(
                                            new Date(trainingPlan.starts_at),
                                        )}{' '}
                                        · {trainingPlan.total_duration_minutes}{' '}
                                        мин.
                                    </p>
                                    <p className="line-clamp-1 text-sm">
                                        {trainingPlan.goal}
                                    </p>
                                    {trainingPlan.training_note && (
                                        <div className="grid gap-2 pt-1">
                                            <p className="text-sm text-muted-foreground">
                                                Нагрузка:{' '}
                                                {
                                                    intensityLabels[
                                                        trainingPlan
                                                            .training_note
                                                            .intensity
                                                    ]
                                                }{' '}
                                                · результат:{' '}
                                                {
                                                    resultLabels[
                                                        trainingPlan
                                                            .training_note
                                                            .result
                                                    ]
                                                }
                                            </p>
                                            <p className="line-clamp-2 text-sm">
                                                {
                                                    trainingPlan.training_note
                                                        .note
                                                }
                                            </p>
                                            {trainingPlan.training_note.tags
                                                .length > 0 && (
                                                <div className="flex flex-wrap gap-1.5">
                                                    {trainingPlan.training_note.tags.map(
                                                        (tag) => (
                                                            <Badge
                                                                key={tag}
                                                                variant="outline"
                                                            >
                                                                {tag}
                                                            </Badge>
                                                        ),
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                <TrainingPlanStatusBadge
                                    status={trainingPlan.status}
                                />
                            </Link>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
