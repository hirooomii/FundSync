<script setup>
import { ref, computed, watch } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard, CreditCard, Landmark, BarChart3, ArrowRightLeft,
    CalendarDays, BookOpen, GitMerge, FileCheck, BookMarked, ScrollText,
    ArrowUpDown, Repeat2, Users, TrendingUp, Activity, ChevronDown,
    Menu, LogOut, User, Bell, FileText, BookOpenCheck,
    Banknote, Wallet, PiggyBank, ClipboardList, FileSearch, Database,
    Settings, Building2, Receipt, Send, CheckSquare, AlertTriangle,
    FileClock, BarChart2
} from 'lucide-vue-next'

const page = usePage()
const sidebarOpen = ref(true)

function isActive(routeName) { try { return route().current(routeName) } catch { return false } }

const navGroups = [
    {
        label: 'Main',
        items: [
            { label: 'Dashboard',       route: 'dashboard',           icon: LayoutDashboard },
            { label: 'Cash Account',    route: 'cashaccount',         icon: CreditCard },
            { label: 'Balances',        route: 'balances.dashboard',  icon: BarChart3 },
            { label: 'Notifications',   route: 'notifications',       icon: Bell },
        ]
    },
    {
        label: 'Depository',
        items: [
            { label: 'Depository Bank',   route: 'depositorybank',              icon: Landmark },
            { label: 'Payment Schedule',  route: 'payment.schedule.calendar',   icon: CalendarDays },
        ]
    },
    {
        label: 'Transactions',
        items: [
            { label: 'Daily Transaction',  route: 'daily.transaction',  icon: ArrowRightLeft },
            { label: 'Acumatica Passbook', route: 'acumatica.passbook', icon: BookOpen },
            { label: 'Unposted Book',      route: 'unposted.book',      icon: BookMarked },
        ]
    },
    {
        label: 'Reconciliation',
        items: [
            { label: 'Reconciliation',   route: 'reconcilliation',  icon: GitMerge },
            { label: 'Multiple Approval',route: 'multiple.approval',icon: CheckSquare },
            { label: 'Multiple Logs',    route: 'multiple.logs',    icon: ScrollText },
            { label: 'SOA Approval',     route: 'soa.approval',     icon: FileCheck },
        ]
    },
    {
        label: 'Fund Transfer',
        items: [
            { label: 'Fund Transfers',   route: 'fund.transfers',   icon: Send },
            { label: 'FTA List',         route: 'fta.list',         icon: ClipboardList },
            { label: 'FT Request',       route: 'ft.request',       icon: FileText },
            { label: 'Answer Approval',  route: 'answer.approval',  icon: CheckSquare },
            { label: 'Create FTAF',      route: 'create.ftaf',      icon: Receipt },
        ]
    },
    {
        label: 'COH & Reports',
        items: [
            { label: 'Overall COH',    route: 'overall.coh',     icon: TrendingUp },
            { label: 'COH Monitoring', route: 'coh.monitoring',  icon: Activity },
            { label: 'Cash Report',    route: 'cash.report',     icon: PiggyBank },
            { label: 'Generate Report',route: 'generate.report', icon: BarChart2 },
        ]
    },
    {
        label: 'Branch',
        items: [
            { label: 'Branch Tagging',        route: 'branch.tagging',        icon: Users },
            { label: 'Branch COH',            route: 'branch.coh',            icon: Wallet },
            { label: 'Branch Report',         route: 'branch.report',         icon: FileSearch },
            { label: 'Branch Reconciliation', route: 'branch.reconciliation', icon: Repeat2 },
        ]
    },
    {
        label: 'Vouchers',
        items: [
            { label: 'Voucher List', route: 'voucher.list', icon: Database },
            { label: 'Voucher Logs', route: 'voucher.logs', icon: FileClock },
        ]
    },
    {
        label: 'Settings & Logs',
        items: [
            { label: 'Approval Matrix', route: 'approval.matrix', icon: Settings },
            { label: 'Cash Position',   route: 'cash.position',   icon: Banknote },
            { label: 'Remark Logs',     route: 'remark.logs',     icon: ScrollText },
            { label: 'Unbind Logs',     route: 'unbind.logs',     icon: AlertTriangle },
        ]
    },
]

function getActiveGroupLabel() {
    for (const group of navGroups) {
        if (group.items.some(i => isActive(i.route))) return group.label
    }
    return 'Main'
}

const openGroups = ref([getActiveGroupLabel()])

watch(() => page.url, () => {
    openGroups.value = [getActiveGroupLabel()]
})

function toggleGroup(label) {
    if (openGroups.value.includes(label)) {
        openGroups.value = []
    } else {
        openGroups.value = [label]
    }
}
function isGroupOpen(label) { return openGroups.value.includes(label) }
function hasActiveChild(items) { return items.some(i => isActive(i.route)) }
</script>

<template>
    <div class="flex h-screen overflow-hidden" style="background: #0f1623;">

        <!-- Mobile overlay -->
        <div v-if="sidebarOpen" class="fixed inset-0 bg-black/60 z-20 lg:hidden" @click="sidebarOpen = false" />

        <!-- ── Sidebar ── -->
        <aside
            class="fixed lg:static inset-y-0 left-0 z-30 flex flex-col transition-all duration-300 shrink-0"
            style="background: #111827; border-right: 1px solid rgba(255,255,255,0.06);"
            :class="sidebarOpen ? 'w-60' : 'w-0 lg:w-14 overflow-hidden'"
        >
            <!-- Logo row -->
            <div class="flex items-center gap-2.5 px-4 py-4 shrink-0" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <Link :href="route('dashboard')" class="flex items-center gap-2.5 min-w-0 flex-1">
                    <ApplicationLogo class="w-7 h-7 shrink-0" />
                    <span v-if="sidebarOpen" class="font-bold text-white text-[15px] tracking-wide truncate">FundSync</span>
                </Link>
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="hidden lg:flex items-center justify-center w-7 h-7 rounded-md hover:bg-white/10 text-gray-500 hover:text-white transition-colors shrink-0"
                >
                    <Menu class="w-3.5 h-3.5" />
                </button>
            </div>

            <!-- Nav scroll area -->
            <nav class="flex-1 overflow-y-auto py-2 space-y-0.5" style="scrollbar-width: thin; scrollbar-color: #374151 transparent;">
                <template v-for="group in navGroups" :key="group.label">
                    <!-- Section toggle -->
                    <button
                        v-if="sidebarOpen"
                        @click="toggleGroup(group.label)"
                        class="w-full flex items-center justify-between px-4 pt-4 pb-1.5 select-none"
                    >
                        <span class="text-[10px] font-bold uppercase tracking-[0.12em] transition-colors"
                            :class="hasActiveChild(group.items) ? 'text-blue-400' : 'text-gray-600'">
                            {{ group.label }}
                        </span>
                        <ChevronDown
                            class="w-3 h-3 text-gray-600 transition-transform duration-200"
                            :class="isGroupOpen(group.label) ? 'rotate-0' : '-rotate-90'"
                        />
                    </button>
                    <!-- Icon-only divider -->
                    <div v-else class="mx-3 my-2 h-px bg-white/5"></div>

                    <!-- Items -->
                    <Transition name="sidebar-slide">
                        <div v-if="sidebarOpen ? isGroupOpen(group.label) : true" class="space-y-0.5 overflow-hidden">
                            <Link
                                v-for="item in group.items"
                                :key="item.route"
                                :href="route(item.route)"
                                class="flex items-center gap-3 mx-2 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150"
                                :class="isActive(item.route)
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40'
                                    : 'text-gray-400 hover:bg-white/8 hover:text-gray-200'"
                                :title="!sidebarOpen ? item.label : undefined"
                            >
                                <component :is="item.icon"
                                    class="shrink-0 transition-colors"
                                    :class="[isActive(item.route) ? 'text-white' : 'text-gray-500', sidebarOpen ? 'w-4 h-4' : 'w-5 h-5']"
                                />
                                <span v-if="sidebarOpen" class="truncate">{{ item.label }}</span>
                            </Link>
                        </div>
                    </Transition>
                </template>
            </nav>

            <!-- User footer -->
            <div class="shrink-0 p-3" style="border-top: 1px solid rgba(255,255,255,0.06);">
                <div v-if="sidebarOpen" class="flex items-center gap-2.5 px-1 mb-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-md">
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-semibold text-white truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-[11px] text-gray-500 truncate">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>
                <div class="flex gap-1.5" :class="sidebarOpen ? '' : 'flex-col items-center'">
                    <Link :href="route('profile.edit')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs text-gray-400 hover:bg-white/8 hover:text-gray-200 transition-colors"
                    >
                        <User class="w-3.5 h-3.5 shrink-0" />
                        <span v-if="sidebarOpen">Profile</span>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors"
                    >
                        <LogOut class="w-3.5 h-3.5 shrink-0" />
                        <span v-if="sidebarOpen">Log Out</span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ── Main area ── -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden" style="background: #f1f5f9;">

            <!-- Top bar -->
            <header class="flex items-center gap-3 px-6 py-3.5 shrink-0"
                style="background: #fff; border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.05);">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors lg:hidden">
                    <Menu class="w-5 h-5" />
                </button>
                <slot name="header" />
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.sidebar-slide-enter-active,
.sidebar-slide-leave-active {
    transition: max-height 0.25s ease, opacity 0.2s ease;
    max-height: 600px;
    opacity: 1;
    overflow: hidden;
}
.sidebar-slide-enter-from,
.sidebar-slide-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>
