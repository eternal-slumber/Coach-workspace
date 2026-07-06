export type ScheduledTrainingStatus = 'planned' | 'completed' | 'cancelled';
export type ScheduledTrainingColor =
    'blue' | 'green' | 'orange' | 'purple' | 'red' | 'gray';

export type ScheduledTraining = {
    id: number;
    trainee_id: number | null;
    training_group_id: number | null;
    starts_at: string;
    ends_at: string;
    subject_name: string;
    subject_type: 'trainee' | 'training_group';
    location: string;
    status: ScheduledTrainingStatus;
    color: ScheduledTrainingColor;
    notes: string | null;
};

export type SelectionOption = {
    id: number;
    name: string;
};

export type CalendarScheduledTraining = {
    id: number;
    title: string;
    starts_at: string;
    ends_at: string;
    location: string;
    status: ScheduledTrainingStatus;
    color: ScheduledTrainingColor;
    notes: string | null;
    trainee_id: number | null;
    training_group_id: number | null;
    subject_type: 'trainee' | 'training_group';
};
