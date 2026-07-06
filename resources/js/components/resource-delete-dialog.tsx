import { Form } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { RouteFormDefinition } from '@/wayfinder';

type ResourceDeleteDialogProps = {
    form: RouteFormDefinition<'post'>;
    resourceName: string;
};

export default function ResourceDeleteDialog({
    form,
    resourceName,
}: ResourceDeleteDialogProps) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="destructive">Удалить</Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Удалить «{resourceName}»?</DialogTitle>
                    <DialogDescription>
                        Запись будет удалена без возможности восстановления.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Отмена</Button>
                    </DialogClose>

                    <Form {...form}>
                        {({ processing }) => (
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                            >
                                {processing ? 'Удаление…' : 'Удалить'}
                            </Button>
                        )}
                    </Form>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
