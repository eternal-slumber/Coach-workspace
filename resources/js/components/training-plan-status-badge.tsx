import { Badge } from '@/components/ui/badge';
import type { TrainingPlanStatus } from '@/types';

const statusLabels: Record<TrainingPlanStatus, string> = {
    draft: 'Черновик',
    approved: 'Утверждён',
    completed: 'Проведён',
};

export default function TrainingPlanStatusBadge({
    status,
}: {
    status: TrainingPlanStatus;
}) {
    return (
        <Badge variant={status === 'draft' ? 'outline' : 'secondary'}>
            {statusLabels[status]}
        </Badge>
    );
}
