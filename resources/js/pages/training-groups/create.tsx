import { Head } from '@inertiajs/react';
import TrainingGroupController from '@/actions/App/Http/Controllers/TrainingGroupController';
import Heading from '@/components/heading';
import TrainingGroupForm from '@/components/training-group-form';
import { Card, CardContent } from '@/components/ui/card';
import { create, index } from '@/routes/training-groups';

export default function TrainingGroupsCreate() {
    return (
        <>
            <Head title="Новая группа" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Новая группа"
                    description="Добавьте направление, уровень и общую цель"
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <TrainingGroupForm
                            form={TrainingGroupController.store.form()}
                            submitLabel="Создать группу"
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

TrainingGroupsCreate.layout = {
    breadcrumbs: [
        { title: 'Группы', href: index() },
        { title: 'Новая группа', href: create() },
    ],
};
