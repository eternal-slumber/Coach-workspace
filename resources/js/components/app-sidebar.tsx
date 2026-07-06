import { Link } from '@inertiajs/react';
import {
    BookOpen,
    CalendarDays,
    Dumbbell,
    FolderGit2,
    LayoutGrid,
    UserRound,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { calendar, dashboard } from '@/routes';
import { index as exercisesIndex } from '@/routes/exercises';
import { index as scheduledTrainingsIndex } from '@/routes/scheduled-trainings';
import { index as traineesIndex } from '@/routes/trainees';
import { index as trainingGroupsIndex } from '@/routes/training-groups';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Сегодня',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Расписание',
        href: scheduledTrainingsIndex(),
        icon: CalendarDays,
    },
    {
        title: 'Календарь',
        href: calendar(),
        icon: CalendarDays,
    },
    {
        title: 'Клиенты',
        href: traineesIndex(),
        icon: UserRound,
    },
    {
        title: 'Группы',
        href: trainingGroupsIndex(),
        icon: Users,
    },
    {
        title: 'Упражнения',
        href: exercisesIndex(),
        icon: Dumbbell,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
