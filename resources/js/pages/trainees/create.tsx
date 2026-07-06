import { Head } from '@inertiajs/react';
import TraineeController from '@/actions/App/Http/Controllers/TraineeController';
import Heading from '@/components/heading';
import TraineeForm from '@/components/trainee-form';
import { Card, CardContent } from '@/components/ui/card';
import { create, index } from '@/routes/trainees';

export default function TraineesCreate() {
    return (
        <>
            <Head title="Новый клиент" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Новый клиент"
                    description="Добавьте основную информацию, цель и ограничения"
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <TraineeForm
                            form={TraineeController.store.form()}
                            submitLabel="Создать клиента"
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

TraineesCreate.layout = {
    breadcrumbs: [
        { title: 'Клиенты', href: index() },
        { title: 'Новый клиент', href: create() },
    ],
};
