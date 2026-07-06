import { Badge } from '@/components/ui/badge';
import type { ScheduledTrainingStatus } from '@/types';

const statusStyles: Record<ScheduledTrainingStatus, string> = {
    planned:
        'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300',
    completed:
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
    cancelled:
        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
};

export default function ScheduledTrainingStatusBadge({
    status,
}: {
    status: ScheduledTrainingStatus;
}) {
    return (
        <Badge variant="outline" className={statusStyles[status]}>
            Статус: {status}
        </Badge>
    );
}
