<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import Select from '@/Components/Dropdown/Select.vue';

const props = defineProps({
    bug: Object,
    priorities: Array,
    tags: Array,
    users: Array,
});

const priorityLabels = {
    critical: '緊急',
    high: '高',
    medium: '中',
    low: '低',
};

const priorityOptions = computed(() =>
    props.priorities.map(p => ({ value: p, label: priorityLabels[p] || p }))
);

const userOptions = computed(() => [
    { value: '', label: '未指派' },
    ...props.users.map(u => ({ value: u.id, label: u.name })),
]);

const form = useForm({
    title: props.bug.title,
    description: props.bug.description,
    priority: props.bug.priority,
    assignee_id: props.bug.assignee_id || '',
    tags: props.bug.tags?.map(t => t.id) || [],
});

const submit = () => {
    form.put(route('bugs.update', props.bug.id));
};

const toggleTag = (tagId) => {
    const index = form.tags.indexOf(tagId);
    if (index === -1) {
        form.tags.push(tagId);
    } else {
        form.tags.splice(index, 1);
    }
};
</script>

<template>
    <Head title="編輯問題" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('bugs.show', bug.id)" class="text-gray-400 hover:text-gray-900">
                    ←
                </Link>
                <h2 class="text-lg font-medium text-gray-900">編輯問題</h2>
            </div>
        </template>

        <div class="py-8">
            <form @submit.prevent="submit" class="max-w-xl space-y-6">
                <div>
                    <InputLabel for="title" value="標題" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="description" value="描述" />
                    <TextArea
                        id="description"
                        v-model="form.description"
                        class="mt-1"
                        :rows="6"
                        required
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="priority" value="優先級" />
                        <div class="mt-1">
                            <Select
                                v-model="form.priority"
                                :options="priorityOptions"
                                placeholder="選擇優先級"
                            />
                        </div>
                        <InputError :message="form.errors.priority" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="assignee" value="負責人" />
                        <div class="mt-1">
                            <Select
                                v-model="form.assignee_id"
                                :options="userOptions"
                                placeholder="未指派"
                            />
                        </div>
                        <InputError :message="form.errors.assignee_id" class="mt-2" />
                    </div>
                </div>

                <div>
                    <InputLabel value="標籤" />
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="tag in tags"
                            :key="tag.id"
                            type="button"
                            @click="toggleTag(tag.id)"
                            class="inline-flex items-center gap-1.5 rounded-full border-2 px-3 py-1 text-xs"
                            :style="{
                                borderColor: form.tags.includes(tag.id) ? tag.color : '#e5e7eb',
                                color: form.tags.includes(tag.id) ? tag.color : '#4b5563',
                            }"
                        >
                            <span
                                class="h-2 w-2 rounded-full"
                                :style="{ backgroundColor: tag.color }"
                            />
                            {{ tag.name }}
                        </button>
                    </div>
                    <InputError :message="form.errors.tags" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="border border-gray-900 px-4 py-2 text-sm text-gray-900 hover:bg-gray-900 hover:text-white disabled:opacity-50"
                    >
                        儲存
                    </button>
                    <Link :href="route('bugs.show', bug.id)" class="text-sm text-gray-500 hover:text-gray-900">
                        取消
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
