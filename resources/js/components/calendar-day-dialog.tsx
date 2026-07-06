import ScheduledTrainingColorDot from '@/components/scheduled-training-color-dot';
import ScheduledTrainingStatusBadge from '@/components/scheduled-training-status-badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { CalendarScheduledTraining } from '@/types';

type CalendarDayDialogProps = {
    date: Date | null;
    scheduledTrainings: CalendarScheduledTraining[];
    onOpenChange: (open: boolean) => void;
    onSelectTraining: (scheduledTraining: CalendarScheduledTraining) => void;
};

const dateFormatter = new Intl.DateTimeFormat('ru-RU', {
    dateStyle: 'full',
});

const timeFormatter = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
});

export default function CalendarDayDialog({
    date,
    scheduledTrainings,
    onOpenChange,
    onSelectTraining,
}: CalendarDayDialogProps) {
    if (!date) {
        return null;
    }

    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle className="capitalize">
                        {dateFormatter.format(date)}
                    </DialogTitle>
                    <DialogDescription>
                        {scheduledTrainings.length > 0
                            ? `Тренировок: ${scheduledTrainings.length}`
                            : 'На этот день тренировок нет'}
                    </DialogDescription>
                </DialogHeader>

                <div className="grid max-h-[60vh] gap-2 overflow-y-auto pr-1">
                    {scheduledTrainings.map((scheduledTraining) => (
                        <button
                            key={scheduledTraining.id}
                            type="button"
                            className="grid gap-2 rounded-lg border p-3 text-left transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            onClick={() => onSelectTraining(scheduledTraining)}
                        >
                            <div className="flex items-start justify-between gap-3">
                                <span className="flex min-w-0 items-center gap-2 font-medium">
                                    <ScheduledTrainingColorDot
                                        color={scheduledTraining.color}
                                    />
                                    <span className="truncate">
                                        {scheduledTraining.title}
                                    </span>
                                </span>
                                <ScheduledTrainingStatusBadge
                                    status={scheduledTraining.status}
                                />
                            </div>

                            <span className="text-sm text-muted-foreground">
                                {timeFormatter.format(
                                    new Date(scheduledTraining.starts_at),
                                )}{' '}
                                —{' '}
                                {timeFormatter.format(
                                    new Date(scheduledTraining.ends_at),
                                )}{' '}
                                · {scheduledTraining.location}
                            </span>
                        </button>
                    ))}
                </div>
            </DialogContent>
        </Dialog>
    );
}
