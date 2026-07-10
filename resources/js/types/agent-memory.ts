export type AgentMemoryType =
    | 'restriction'
    | 'preference'
    | 'progress'
    | 'risk'
    | 'methodic_note'
    | 'general';

export type AgentMemory = {
    id: number;
    type: AgentMemoryType;
    content: string;
    importance: number;
    is_active: boolean;
};

export type AgentMemorySubject = {
    id: number;
    name: string;
    type: 'trainee' | 'training_group';
};
