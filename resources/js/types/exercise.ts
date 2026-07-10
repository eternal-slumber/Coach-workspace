export type Exercise = {
    id: number;
    user_id: number | null;
    name: string;
    description: string;
    goal: string;
    difficulty: string;
    equipment: string | null;
    duration_minutes: number | null;
    muscle_groups: string[];
    load_type: string | null;
    movement_pattern: string | null;
    contraindications: string | null;
    age_min: number | null;
    age_max: number | null;
    tags: string[];
    is_system: boolean;
};

export type ExerciseListItem = Pick<
    Exercise,
    | 'id'
    | 'user_id'
    | 'name'
    | 'goal'
    | 'difficulty'
    | 'equipment'
    | 'duration_minutes'
    | 'load_type'
    | 'movement_pattern'
    | 'tags'
    | 'is_system'
>;
