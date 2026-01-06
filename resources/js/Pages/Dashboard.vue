<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/Bug/StatusBadge.vue';
import PriorityBadge from '@/Components/Bug/PriorityBadge.vue';

const props = defineProps({
    stats: Object,
    statusCounts: Object,
    priorityCounts: Object,
    dailyBugs: Array,
    assigneeCounts: Array,
    recentBugs: Array,
    recentActivities: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('zh-TW', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const activityMessage = (activity) => {
    const typeLabels = {
        created: '建立了問題',
        status_changed: '將狀態改為',
        priority_changed: '將優先級改為',
        assigned: '指派了問題',
        commented: '新增了留言',
        attachment_added: '新增了附件',
    };

    const valueLabels = {
        open: '開放',
        in_progress: '進行中',
        resolved: '已解決',
        closed: '已關閉',
        low: '低',
        medium: '中',
        high: '高',
        critical: '緊急',
    };

    const base = typeLabels[activity.type] || activity.type;

    if (activity.new_value) {
        const newLabel = valueLabels[activity.new_value] || activity.new_value;
        return `${base} ${newLabel}`;
    }

    return base;
};
</script>

<template>
    <Head title="儀表板" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">儀表板</h2>
        </template>

        <div class="py-8">
            <!-- Stats -->
            <div class="grid grid-cols-5 gap-px border border-gray-100 bg-gray-100">
                <div class="bg-white p-6">
                    <div class="text-xs text-gray-500">總問題數</div>
                    <div class="mt-1 text-2xl font-medium text-gray-900">{{ stats.total }}</div>
                </div>
                <div class="bg-white p-6">
                    <div class="text-xs text-gray-500">開放</div>
                    <div class="mt-1 text-2xl font-medium text-gray-900">{{ stats.open }}</div>
                </div>
                <div class="bg-white p-6">
                    <div class="text-xs text-gray-500">進行中</div>
                    <div class="mt-1 text-2xl font-medium text-gray-900">{{ stats.inProgress }}</div>
                </div>
                <div class="bg-white p-6">
                    <div class="text-xs text-gray-500">已解決</div>
                    <div class="mt-1 text-2xl font-medium text-gray-900">{{ stats.resolved }}</div>
                </div>
                <div class="bg-white p-6">
                    <div class="text-xs text-gray-500">已關閉</div>
                    <div class="mt-1 text-2xl font-medium text-gray-900">{{ stats.closed }}</div>
                </div>
            </div>

            <div class="mt-12 grid gap-12 lg:grid-cols-2">
                <!-- Recent Bugs -->
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900">最近問題</h3>
                        <Link :href="route('bugs.index')" class="text-xs text-gray-500 hover:text-gray-900">
                            查看全部
                        </Link>
                    </div>
                    <div class="mt-4 divide-y divide-gray-100">
                        <Link
                            v-for="bug in recentBugs"
                            :key="bug.id"
                            :href="route('bugs.show', bug.id)"
                            class="block py-4"
                        >
                            <div class="flex items-center gap-3">
                                <StatusBadge :status="bug.status" />
                                <span class="text-gray-300">·</span>
                                <PriorityBadge :priority="bug.priority" />
                                <template v-if="bug.tags?.length">
                                    <span class="text-gray-300">·</span>
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-for="tag in bug.tags"
                                            :key="tag.id"
                                            class="inline-flex items-center gap-1 text-xs text-gray-600"
                                        >
                                            <span
                                                class="h-2 w-2 rounded-full"
                                                :style="{ backgroundColor: tag.color }"
                                            />
                                            {{ tag.name }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-2 text-sm text-gray-900">{{ bug.title }}</div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ bug.reporter?.name }}
                                <span v-if="bug.assignee"> → {{ bug.assignee.name }}</span>
                            </div>
                        </Link>
                        <p v-if="!recentBugs?.length" class="py-8 text-center text-sm text-gray-400">
                            尚無問題
                        </p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900">最近活動</h3>
                    <div class="mt-4 divide-y divide-gray-100">
                        <div
                            v-for="activity in recentActivities"
                            :key="activity.id"
                            class="py-4"
                        >
                            <div class="text-sm">
                                <span class="font-medium text-blue-600">{{ activity.user?.name }}</span>
                                <p class="mt-0.5 text-gray-600">{{ activityMessage(activity) }}</p>
                            </div>
                            <Link
                                v-if="activity.bug"
                                :href="route('bugs.show', activity.bug.id)"
                                class="mt-1 block text-xs text-gray-500 hover:text-gray-900"
                            >
                                {{ activity.bug.title }}
                            </Link>
                            <p class="mt-1 text-xs text-gray-400">{{ formatDate(activity.created_at) }}</p>
                        </div>
                        <p v-if="!recentActivities?.length" class="py-8 text-center text-sm text-gray-400">
                            尚無活動
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Action -->
            <div class="mt-12">
                <Link
                    :href="route('bugs.create')"
                    class="inline-flex items-center border border-gray-900 px-4 py-2 text-sm text-gray-900 hover:bg-gray-900 hover:text-white"
                >
                    回報新問題
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
