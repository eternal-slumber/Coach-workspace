import { Link } from '@inertiajs/react';
import {
    BookOpen,
    CalendarDays,
    ClipboardList,
    Dumbbell,
    FolderGit2,
    Home,
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
import { index as traineesIndex } from '@/routes/trainees';
import { index as trainingGroupsIndex } from '@/routes/training-groups';
import { index as trainingPlansIndex } from '@/routes/training-plans';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Обзор',
        href: dashboard(),
        icon: Home,
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
    {
        title: 'Планы тренировок',
        href: trainingPlansIndex(),
        icon: ClipboardList,
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
