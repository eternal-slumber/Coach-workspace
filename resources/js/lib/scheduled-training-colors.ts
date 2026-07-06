import type { ScheduledTrainingColor } from '@/types';

type ScheduledTrainingColorOption = {
    value: ScheduledTrainingColor;
    label: string;
    swatchClassName: string;
    calendarColors: {
        lineColor: string;
        eventColor: string;
        eventSelectedColor: string;
        textColor: string;
    };
};

export const scheduledTrainingColorOptions: ScheduledTrainingColorOption[] = [
    {
        value: 'blue',
        label: 'Синий',
        swatchClassName: 'bg-blue-500',
        calendarColors: {
            lineColor: '#2563eb',
            eventColor: '#dbeafe',
            eventSelectedColor: '#bfdbfe',
            textColor: '#1e3a8a',
        },
    },
    {
        value: 'green',
        label: 'Зелёный',
        swatchClassName: 'bg-green-500',
        calendarColors: {
            lineColor: '#16a34a',
            eventColor: '#dcfce7',
            eventSelectedColor: '#bbf7d0',
            textColor: '#14532d',
        },
    },
    {
        value: 'orange',
        label: 'Оранжевый',
        swatchClassName: 'bg-orange-500',
        calendarColors: {
            lineColor: '#ea580c',
            eventColor: '#ffedd5',
            eventSelectedColor: '#fed7aa',
            textColor: '#7c2d12',
        },
    },
    {
        value: 'purple',
        label: 'Фиолетовый',
        swatchClassName: 'bg-purple-500',
        calendarColors: {
            lineColor: '#9333ea',
            eventColor: '#f3e8ff',
            eventSelectedColor: '#e9d5ff',
            textColor: '#581c87',
        },
    },
    {
        value: 'red',
        label: 'Красный',
        swatchClassName: 'bg-red-500',
        calendarColors: {
            lineColor: '#dc2626',
            eventColor: '#fee2e2',
            eventSelectedColor: '#fecaca',
            textColor: '#7f1d1d',
        },
    },
    {
        value: 'gray',
        label: 'Серый',
        swatchClassName: 'bg-gray-500',
        calendarColors: {
            lineColor: '#6b7280',
            eventColor: '#f3f4f6',
            eventSelectedColor: '#e5e7eb',
            textColor: '#1f2937',
        },
    },
];

export function getScheduledTrainingColor(
    color: ScheduledTrainingColor,
): ScheduledTrainingColorOption {
    return (
        scheduledTrainingColorOptions.find(
            (colorOption) => colorOption.value === color,
        ) ?? scheduledTrainingColorOptions[0]
    );
}
