import { getScheduledTrainingColor } from '@/lib/scheduled-training-colors';
import { cn } from '@/lib/utils';
import type { ScheduledTrainingColor } from '@/types';

type ScheduledTrainingColorDotProps = {
    color: ScheduledTrainingColor;
    className?: string;
};

export default function ScheduledTrainingColorDot({
    color,
    className,
}: ScheduledTrainingColorDotProps) {
    const colorOption = getScheduledTrainingColor(color);

    return (
        <span
            className={cn(
                'inline-block size-2.5 shrink-0 rounded-full',
                colorOption.swatchClassName,
                className,
            )}
            aria-label={`Цвет: ${colorOption.label}`}
        />
    );
}
