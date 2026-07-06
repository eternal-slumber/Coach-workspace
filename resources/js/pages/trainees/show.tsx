import { Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import TraineeController from '@/actions/App/Http/Controllers/TraineeController';
import ResourceDeleteDialog from '@/components/resource-delete-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { edit, index } from '@/routes/trainees';
import type { Trainee } from '@/types';

type TraineesShowProps = {
    trainee: Trainee;
};

function Detail({
    label,
    value,
}: {
    label: string;
    value: string | number | null;
}) {
    return (
        <div className="grid gap-1">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd className="whitespace-pre-wrap">{value || 'Не указано'}</dd>
        </div>
    );
}

export default function TraineesShow({ trainee }: TraineesShowProps) {
    return (
        <>
            <Head title={trainee.name} />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {trainee.name}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Карточка клиента
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-3">
                        <Button variant="outline" asChild>
                            <Link href={edit(trainee)}>
                                <Pencil />
                                Редактировать
                            </Link>
                        </Button>
                        <ResourceDeleteDialog
                            form={TraineeController.destroy.form(trainee)}
                            resourceName={trainee.name}
                        />
                    </div>
                </div>

                <Card className="max-w-3xl">
                    <CardContent>
                        <dl className="grid gap-6 sm:grid-cols-2">
                            <Detail label="Возраст" value={trainee.age} />
                            <Detail label="Уровень" value={trainee.level} />
                            <div className="sm:col-span-2">
                                <Detail label="Цель" value={trainee.goal} />
                            </div>
                            <div className="sm:col-span-2">
                                <Detail
                                    label="Ограничения"
                                    value={trainee.restrictions}
                                />
                            </div>
                            <div className="sm:col-span-2">
                                <Detail label="Заметки" value={trainee.notes} />
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

TraineesShow.layout = {
    breadcrumbs: [{ title: 'Клиенты', href: index() }],
};
