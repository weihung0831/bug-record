<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/Bug/StatusBadge.vue';
import PriorityBadge from '@/Components/Bug/PriorityBadge.vue';
import Select from '@/Components/Dropdown/Select.vue';

const props = defineProps({
    bugs: Object,
    filters: Object,
    statuses: Array,
    priorities: Array,
    tags: Array,
    users: Array,
});

const statusLabels = {
    open: '開放',
    in_progress: '進行中',
    resolved: '已解決',
    closed: '已關閉',
};

const priorityLabels = {
    critical: '緊急',
    high: '高',
    medium: '中',
    low: '低',
};

const statusOptions = computed(() => [
    { value: '', label: '所有狀態' },
    ...props.statuses.map(s => ({ value: s, label: statusLabels[s] || s })),
]);

const priorityOptions = computed(() => [
    { value: '', label: '所有優先級' },
    ...props.priorities.map(p => ({ value: p, label: priorityLabels[p] || p })),
]);

const tagOptions = computed(() => [
    { value: '', label: '所有標籤' },
    ...(props.tags || []).map(t => ({ value: t.slug, label: t.name })),
]);

const localFilters = ref({ ...props.filters });

const applyFilters = () => {
    router.get(route('bugs.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    localFilters.value = {};
    applyFilters();
};

watch(localFilters, applyFilters, { deep: true });
</script>

<template>
    <Head title="問題" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">問題</h2>
                <Link
                    :href="route('bugs.create')"
                    class="border border-gray-900 px-3 py-1.5 text-sm text-gray-900 hover:bg-gray-900 hover:text-white"
                >
                    新增
                </Link>
            </div>
        </template>

        <div class="py-8">
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4 border-b border-gray-100 pb-6">
                <input
                    v-model="localFilters.search"
                    type="text"
                    placeholder="搜尋..."
                    class="w-48 border-0 bg-transparent px-0 py-2 text-sm focus:outline-none focus:ring-0"
                />
                <div class="w-32">
                    <Select
                        v-model="localFilters.status"
                        :options="statusOptions"
                        placeholder="所有狀態"
                    />
                </div>
                <div class="w-32">
                    <Select
                        v-model="localFilters.priority"
                        :options="priorityOptions"
                        placeholder="所有優先級"
                    />
                </div>
                <div class="w-32">
                    <Select
                        v-model="localFilters.tag"
                        :options="tagOptions"
                        placeholder="所有標籤"
                    />
                </div>
                <button
                    v-if="Object.values(localFilters).some(v => v)"
                    @click="clearFilters"
                    class="text-xs text-gray-500 hover:text-gray-900"
                >
                    清除
                </button>
            </div>

            <!-- Bug List -->
            <div class="divide-y divide-gray-100">
                <Link
                    v-for="bug in bugs.data"
                    :key="bug.id"
                    :href="route('bugs.show', bug.id)"
                    class="block py-6"
                >
                    <div class="flex items-start justify-between">
                        <div>
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
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ bug.title }}</h3>
                            <p class="mt-1 line-clamp-1 text-sm text-gray-500">{{ bug.description }}</p>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <div>{{ bug.reporter?.name }}</div>
                            <div v-if="bug.assignee" class="mt-1">→ {{ bug.assignee.name }}</div>
                        </div>
                    </div>
                </Link>

                <div v-if="bugs.data.length === 0" class="py-16 text-center">
                    <p class="text-sm text-gray-400">找不到問題</p>
                    <Link
                        :href="route('bugs.create')"
                        class="mt-4 inline-block text-sm text-gray-900 hover:underline"
                    >
                        建立第一個問題
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="bugs.links.length > 3" class="mt-8 flex justify-center gap-1">
                <template v-for="(link, index) in bugs.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3 py-1 text-sm"
                        :class="link.active ? 'text-gray-900' : 'text-gray-400 hover:text-gray-900'"
                        v-html="link.label"
                    />
                    <span v-else class="px-3 py-1 text-sm text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
