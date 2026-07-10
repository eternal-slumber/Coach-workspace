import { createDragPlugin } from '@dayflow/plugin-drag';
import {
    createDayView,
    createEvent,
    createEventsPlugin,
    createMonthView,
    createWeekView,
    DayFlowCalendar,
    temporalToDate,
    useCalendarApp,
    ViewType,
} from '@dayflow/react';
import type { Event } from '@dayflow/react';
import { Head, Link, router, useHttp } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { CSSProperties } from 'react';
import { useState } from 'react';
import { toast } from 'sonner';
import CalendarDayDialog from '@/components/calendar-day-dialog';
import CalendarTrainingDialog from '@/components/calendar-training-dialog';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
import { scheduledTrainingColorOptions } from '@/lib/scheduled-training-colors';
import { calendar } from '@/routes';
import {
    create as createScheduledTraining,
    schedule,
} from '@/routes/scheduled-trainings';
import type { CalendarScheduledTraining } from '@/types';

type CalendarPageProps = {
    scheduledTrainings: CalendarScheduledTraining[];
    workingHours: {
        startsAt: string;
        endsAt: string;
    };
};

type ScheduleUpdate = {
    starts_at: string;
    ends_at: string;
};

const calendarHourHeight = 72;

export default function CalendarPage({
    scheduledTrainings,
    workingHours,
}: CalendarPageProps) {
    const { resolvedAppearance } = useAppearance();
    const [selectedTraining, setSelectedTraining] =
        useState<CalendarScheduledTraining | null>(null);
    const [selectedDay, setSelectedDay] = useState<Date | null>(null);
    const scheduleUpdate = useHttp<ScheduleUpdate, ScheduleUpdate>({
        starts_at: '',
        ends_at: '',
    });
    const events = scheduledTrainings.map(toDayFlowEvent);
    const { firstHour, lastHour, visibleHourSlots } =
        getWorkingHourRange(workingHours);
    const visibleRangeHeight = calendarHourHeight * visibleHourSlots + 12;
    const eventsVersion = scheduledTrainings
        .map(({ id, starts_at, ends_at }) => `${id}:${starts_at}:${ends_at}`)
        .join('|');

    const persistEventTimes = async (updatedEvent: Event): Promise<void> => {
        scheduleUpdate.setData({
            starts_at: temporalToDate(updatedEvent.start).toISOString(),
            ends_at: temporalToDate(updatedEvent.end).toISOString(),
        });

        let requestFailed = false;

        try {
            const response = await scheduleUpdate.patch(
                schedule.url(Number(updatedEvent.id)),
                {
                    onError: () => {
                        requestFailed = true;
                    },
                },
            );

            if (requestFailed || response === undefined) {
                reloadCalendarAfterFailure();

                return;
            }

            toast.success('Время тренировки обновлено');
        } catch {
            reloadCalendarAfterFailure();
        }
    };
    const dragPlugin = createDragPlugin({
        enableDrag: true,
        enableResize: true,
        enableCreate: false,
        enableAllDayCreate: false,
        onEventDrop: persistEventTimes,
        onEventResize: persistEventTimes,
    });
    const dayFlowCalendar = useCalendarApp(
        {
            views: [
                createDayView({
                    firstHour,
                    lastHour,
                    hourHeight: calendarHourHeight,
                }),
                createWeekView({
                    firstHour,
                    lastHour,
                    hourHeight: calendarHourHeight,
                    gridDateClick: 'none',
                    gridDateDoubleClick: 'none',
                }),
                createMonthView({
                    eventHeight: 14,
                    gridDateClick: 'none',
                    gridDateDoubleClick: 'none',
                }),
            ],
            defaultView: ViewType.WEEK,
            initialDate: new Date(),
            events,
            plugins: [dragPlugin, createEventsPlugin()],
            calendars: scheduledTrainingColorOptions.map((colorOption) => ({
                id: colorOption.value,
                name: colorOption.label,
                colors: colorOption.calendarColors,
            })),
            callbacks: {
                onEventClick: (event) => {
                    const scheduledTraining = scheduledTrainings.find(
                        ({ id }) => id === Number(event.id),
                    );

                    setSelectedTraining(scheduledTraining ?? null);
                },
                onMoreEventsClick: (date) => setSelectedDay(date),
            },
            locale: 'ru-RU',
            switcherMode: 'buttons',
            theme: { mode: resolvedAppearance },
            useCalendarHeader: true,
            useEventDetailDialog: false,
            useEventDetailPanel: false,
        },
        `${eventsVersion}:${resolvedAppearance}:${firstHour}:${lastHour}`,
    );

    return (
        <>
            <Head title="Календарь" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Календарь"
                        description="Перетащите тренировку или измените её длительность"
                    />
                    <Button asChild>
                        <Link href={createScheduledTraining()}>
                            <Plus />
                            Добавить тренировку
                        </Link>
                    </Button>
                </div>

                <section
                    className={`coach-calendar overflow-hidden rounded-xl border bg-card ${
                        dayFlowCalendar.currentView === ViewType.MONTH
                            ? 'coach-calendar-month'
                            : ''
                    }`}
                    style={
                        {
                            '--coach-calendar-visible-height': `${visibleRangeHeight}px`,
                        } as CSSProperties
                    }
                    aria-label="Календарь тренировок"
                >
                    <DayFlowCalendar calendar={dayFlowCalendar} />
                </section>

                <CalendarTrainingDialog
                    scheduledTraining={selectedTraining}
                    onOpenChange={(open) => {
                        if (!open) {
                            setSelectedTraining(null);
                        }
                    }}
                />

                <CalendarDayDialog
                    date={selectedDay}
                    scheduledTrainings={getTrainingsForDay(
                        scheduledTrainings,
                        selectedDay,
                    )}
                    onOpenChange={(open) => {
                        if (!open) {
                            setSelectedDay(null);
                        }
                    }}
                    onSelectTraining={(scheduledTraining) => {
                        setSelectedDay(null);
                        setSelectedTraining(scheduledTraining);
                    }}
                />
            </main>
        </>
    );
}

CalendarPage.layout = {
    breadcrumbs: [{ title: 'Календарь', href: calendar() }],
};

function toDayFlowEvent(scheduledTraining: CalendarScheduledTraining): Event {
    return createEvent({
        id: scheduledTraining.id.toString(),
        title: scheduledTraining.title,
        description: scheduledTraining.notes ?? scheduledTraining.location,
        start: new Date(scheduledTraining.starts_at),
        end: new Date(scheduledTraining.ends_at),
        calendarId: scheduledTraining.color,
        meta: {
            status: scheduledTraining.status,
            location: scheduledTraining.location,
            trainee_id: scheduledTraining.trainee_id,
            training_group_id: scheduledTraining.training_group_id,
            type: scheduledTraining.subject_type,
        },
    });
}

function timeToDecimalHour(time: string): number {
    const [hours = 0, minutes = 0] = time.split(':').map(Number);

    return hours + minutes / 60;
}

function getWorkingHourRange(
    workingHours: CalendarPageProps['workingHours'],
): {
    firstHour: number;
    lastHour: number;
    visibleHourSlots: number;
} {
    const firstHour = clampHour(timeToDecimalHour(workingHours.startsAt), 0, 23);
    const requestedLastHour = clampHour(
        timeToDecimalHour(workingHours.endsAt),
        firstHour + 1,
        24,
    );
    const lastHour =
        requestedLastHour > firstHour ? requestedLastHour : firstHour + 1;

    return {
        firstHour,
        lastHour,
        visibleHourSlots: Math.min(24, lastHour + 1) - firstHour,
    };
}

function clampHour(hour: number, min: number, max: number): number {
    if (!Number.isFinite(hour)) {
        return min;
    }

    return Math.min(max, Math.max(min, hour));
}

function reloadCalendarAfterFailure(): void {
    router.reload({ only: ['scheduledTrainings'] });
    toast.error('Не удалось изменить время тренировки');
}

function getTrainingsForDay(
    scheduledTrainings: CalendarScheduledTraining[],
    date: Date | null,
): CalendarScheduledTraining[] {
    if (!date) {
        return [];
    }

    const dayStart = new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate(),
    );
    const dayEnd = new Date(dayStart);
    dayEnd.setDate(dayEnd.getDate() + 1);

    return scheduledTrainings
        .filter((scheduledTraining) => {
            const startsAt = new Date(scheduledTraining.starts_at);
            const endsAt = new Date(scheduledTraining.ends_at);

            return startsAt < dayEnd && endsAt > dayStart;
        })
        .sort(
            (firstTraining, secondTraining) =>
                new Date(firstTraining.starts_at).getTime() -
                new Date(secondTraining.starts_at).getTime(),
        );
}
