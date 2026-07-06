import { Head } from '@inertiajs/react';
import TrainingGroupController from '@/actions/App/Http/Controllers/TrainingGroupController';
import Heading from '@/components/heading';
import TrainingGroupForm from '@/components/training-group-form';
import { Card, CardContent } from '@/components/ui/card';
import { index } from '@/routes/training-groups';
import type { TrainingGroup } from '@/types';

type TrainingGroupsEditProps = {
    trainingGroup: TrainingGroup;
};

export default function TrainingGroupsEdit({
    trainingGroup,
}: TrainingGroupsEditProps) {
    return (
        <>
            <Head title={`Редактирование — ${trainingGroup.name}`} />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Редактирование группы"
                    description={trainingGroup.name}
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <TrainingGroupForm
                            form={TrainingGroupController.update.form.patch(
                                trainingGroup,
                            )}
                            submitLabel="Сохранить изменения"
                            trainingGroup={trainingGroup}
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

TrainingGroupsEdit.layout = {
    breadcrumbs: [{ title: 'Группы', href: index() }],
};
