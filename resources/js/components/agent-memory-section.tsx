import { Form } from '@inertiajs/react';
import { Brain, Pencil, Plus } from 'lucide-react';
import { useState } from 'react';
import {
    store,
    update,
} from '@/actions/App/Http/Controllers/AgentMemoryController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { AgentMemory, AgentMemorySubject, AgentMemoryType } from '@/types';

const typeLabels: Record<AgentMemoryType, string> = {
    restriction: 'Ограничение',
    preference: 'Предпочтение',
    progress: 'Прогресс',
    risk: 'Риск',
    methodic_note: 'Методическая заметка',
    general: 'Общее',
};

const selectClassName =
    'border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]';

function AgentMemoryForm({
    subject,
    agentMemory,
    onClose,
}: {
    subject: AgentMemorySubject;
    agentMemory?: AgentMemory;
    onClose: () => void;
}) {
    const form = agentMemory ? update.form.patch(agentMemory) : store.form();

    return (
        <Form
            {...form}
            onSuccess={onClose}
            className="grid gap-5 rounded-lg border bg-muted/20 p-4"
        >
            {({ processing, errors }) => (
                <>
                    {!agentMemory && (
                        <input
                            type="hidden"
                            name={
                                subject.type === 'trainee'
                                    ? 'trainee_id'
                                    : 'training_group_id'
                            }
                            value={subject.id}
                        />
                    )}

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="agent-memory-type">Тип</Label>
                            <select
                                id="agent-memory-type"
                                name="type"
                                className={selectClassName}
                                defaultValue={agentMemory?.type ?? 'general'}
                            >
                                {Object.entries(typeLabels).map(
                                    ([value, label]) => (
                                        <option key={value} value={value}>
                                            {label}
                                        </option>
                                    ),
                                )}
                            </select>
                            <InputError message={errors.type} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="agent-memory-importance">
                                Важность, 1–10
                            </Label>
                            <Input
                                id="agent-memory-importance"
                                name="importance"
                                type="number"
                                min={1}
                                max={10}
                                defaultValue={agentMemory?.importance ?? 5}
                                required
                            />
                            <InputError message={errors.importance} />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="agent-memory-content">
                            Важный факт
                        </Label>
                        <Textarea
                            id="agent-memory-content"
                            name="content"
                            defaultValue={agentMemory?.content ?? ''}
                            placeholder="Например: не давать длительную прыжковую нагрузку"
                            className="min-h-28"
                            required
                        />
                        <InputError message={errors.content} />
                        <InputError message={errors.trainee_id} />
                        <InputError message={errors.training_group_id} />
                    </div>

                    <div className="grid max-w-xs gap-2">
                        <Label htmlFor="agent-memory-active">Состояние</Label>
                        <select
                            id="agent-memory-active"
                            name="is_active"
                            className={selectClassName}
                            defaultValue={
                                agentMemory?.is_active === false ? '0' : '1'
                            }
                        >
                            <option value="1">Активна</option>
                            <option value="0">Отключена</option>
                        </select>
                        <InputError message={errors.is_active} />
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing
                                ? 'Сохранение…'
                                : agentMemory
                                  ? 'Сохранить изменения'
                                  : 'Добавить память'}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Отмена
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}

export default function AgentMemorySection({
    subject,
    agentMemories,
}: {
    subject: AgentMemorySubject;
    agentMemories: AgentMemory[];
}) {
    const [editor, setEditor] = useState<'new' | number | null>(null);

    return (
        <Card className="max-w-3xl">
            <CardHeader className="flex-row items-center justify-between gap-3">
                <div className="grid gap-1">
                    <CardTitle className="flex items-center gap-2">
                        <Brain className="size-5" />
                        Важная память
                    </CardTitle>
                    <p className="text-sm text-muted-foreground">
                        Долгосрочные особенности для будущих тренировок
                    </p>
                </div>
                {editor === null && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => setEditor('new')}
                    >
                        <Plus />
                        Добавить факт
                    </Button>
                )}
            </CardHeader>
            <CardContent className="grid gap-4">
                {editor === 'new' && (
                    <AgentMemoryForm
                        subject={subject}
                        onClose={() => setEditor(null)}
                    />
                )}

                {agentMemories.length === 0 && editor === null ? (
                    <p className="text-sm text-muted-foreground">
                        Важных фактов пока нет
                    </p>
                ) : (
                    <div className="grid gap-3">
                        {agentMemories.map((agentMemory) =>
                            editor === agentMemory.id ? (
                                <AgentMemoryForm
                                    key={agentMemory.id}
                                    subject={subject}
                                    agentMemory={agentMemory}
                                    onClose={() => setEditor(null)}
                                />
                            ) : (
                                <article
                                    key={agentMemory.id}
                                    className={`grid gap-3 rounded-lg border p-4 ${
                                        agentMemory.is_active
                                            ? ''
                                            : 'opacity-60'
                                    }`}
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div className="flex flex-wrap gap-2">
                                            <Badge variant="outline">
                                                {typeLabels[agentMemory.type]}
                                            </Badge>
                                            <Badge variant="secondary">
                                                Важность:{' '}
                                                {agentMemory.importance}/10
                                            </Badge>
                                            {!agentMemory.is_active && (
                                                <Badge variant="outline">
                                                    Отключена
                                                </Badge>
                                            )}
                                        </div>
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            onClick={() =>
                                                setEditor(agentMemory.id)
                                            }
                                        >
                                            <Pencil />
                                            Редактировать
                                        </Button>
                                    </div>
                                    <p className="whitespace-pre-wrap">
                                        {agentMemory.content}
                                    </p>
                                </article>
                            ),
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
