import { Form, Link } from '@inertiajs/react';
import { Copy, Pencil } from 'lucide-react';
import ResourceDeleteDialog from '@/components/resource-delete-dialog';
import ScheduledTrainingColorDot from '@/components/scheduled-training-color-dot';
import ScheduledTrainingStatusBadge from '@/components/scheduled-training-status-badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { destroy, duplicate, edit } from '@/routes/scheduled-trainings';
import type { CalendarScheduledTraining } from '@/types';

type CalendarTrainingDialogProps = {
    scheduledTraining: CalendarScheduledTraining | null;
    onOpenChange: (open: boolean) => void;
};

const timeFormatter = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
});

export default function CalendarTrainingDialog({
    scheduledTraining,
    onOpenChange,
}: CalendarTrainingDialogProps) {
    if (!scheduledTraining) {
        return null;
    }

    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 pr-6">
                        <ScheduledTrainingColorDot
                            color={scheduledTraining.color}
                        />
                        {scheduledTraining.title}
                    </DialogTitle>
                    <DialogDescription>
                        {scheduledTraining.subject_type === 'trainee'
                            ? 'Индивидуальная тренировка'
                            : 'Групповая тренировка'}
                    </DialogDescription>
                </DialogHeader>

                <dl className="grid gap-4 py-2 sm:grid-cols-2">
                    <div className="grid gap-1">
                        <dt className="text-sm text-muted-foreground">Время</dt>
                        <dd className="font-medium tabular-nums">
                            {timeFormatter.format(
                                new Date(scheduledTraining.starts_at),
                            )}{' '}
                            —{' '}
                            {timeFormatter.format(
                                new Date(scheduledTraining.ends_at),
                            )}
                        </dd>
                    </div>
                    <div className="grid gap-1">
                        <dt className="text-sm text-muted-foreground">
                            Статус
                        </dt>
                        <dd>
                            <ScheduledTrainingStatusBadge
                                status={scheduledTraining.status}
                            />
                        </dd>
                    </div>
                    <div className="grid gap-1 sm:col-span-2">
                        <dt className="text-sm text-muted-foreground">
                            Локация
                        </dt>
                        <dd>{scheduledTraining.location}</dd>
                    </div>
                </dl>

                <div className="flex flex-wrap gap-2 border-t pt-4">
                    <Button variant="outline" asChild>
                        <Link href={edit(scheduledTraining.id)}>
                            <Pencil />
                            Редактировать
                        </Link>
                    </Button>

                    <Form {...duplicate.form(scheduledTraining.id)}>
                        {({ processing }) => (
                            <Button
                                type="submit"
                                variant="outline"
                                disabled={processing}
                            >
                                <Copy />
                                {processing
                                    ? 'Дублирование…'
                                    : 'Дублировать на следующую неделю'}
                            </Button>
                        )}
                    </Form>

                    <ResourceDeleteDialog
                        form={destroy.form(scheduledTraining.id, {
                            query: { redirect: 'calendar' },
                        })}
                        resourceName={scheduledTraining.title}
                    />
                </div>
            </DialogContent>
        </Dialog>
    );
}
