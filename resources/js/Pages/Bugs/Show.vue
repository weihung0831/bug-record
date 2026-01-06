<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/Bug/StatusBadge.vue';
import PriorityBadge from '@/Components/Bug/PriorityBadge.vue';
import Modal from '@/Components/Modal.vue';
import TextArea from '@/Components/TextArea.vue';

const props = defineProps({
    bug: Object,
    statuses: Array,
    priorities: Array,
    tags: Array,
    users: Array,
    can: Object,
});

const activeTab = ref('comments');
const showDeleteModal = ref(false);
const fileInputRef = ref(null);
const commentFileRef = ref(null);
const isImage = (attachment) => {
    return attachment.mime_type?.startsWith('image/');
};

const openPreview = (attachment) => {
    if (isImage(attachment)) {
        window.open(attachment.url, '_blank');
    } else {
        window.location.href = route('attachments.download', attachment.id);
    }
};

const commentForm = useForm({
    body: '',
    parent_id: null,
    file: null,
});

const attachmentForm = useForm({
    file: null,
});

const submitComment = () => {
    commentForm.post(route('comments.store', props.bug.id), {
        onSuccess: () => {
            commentForm.reset();
            if (commentFileRef.value) commentFileRef.value.value = '';
        },
        preserveScroll: true,
        forceFormData: true,
    });
};

const uploadAttachment = () => {
    attachmentForm.post(route('attachments.store', props.bug.id), {
        onSuccess: () => {
            attachmentForm.reset();
            if (fileInputRef.value) fileInputRef.value.value = '';
        },
        forceFormData: true,
    });
};

const updateStatus = (status) => {
    router.put(route('bugs.update', props.bug.id), { status }, {
        preserveScroll: true,
    });
};

const deleteBug = () => {
    router.delete(route('bugs.destroy', props.bug.id));
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('zh-TW', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const statusLabels = {
    open: '開放',
    in_progress: '進行中',
    resolved: '已解決',
    closed: '已關閉',
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
        ...statusLabels,
        low: '低',
        medium: '中',
        high: '高',
        critical: '緊急',
    };

    const base = typeLabels[activity.type] || activity.type;

    if (activity.old_value && activity.new_value) {
        const oldLabel = valueLabels[activity.old_value] || activity.old_value;
        const newLabel = valueLabels[activity.new_value] || activity.new_value;
        return `${base} ${oldLabel} → ${newLabel}`;
    }

    return base;
};
</script>

<template>
    <Head :title="bug.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('bugs.index')" class="text-gray-400 hover:text-gray-900">
                    ←
                </Link>
                <h2 class="text-lg font-medium text-gray-900">問題詳情</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Bug Info -->
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
                        <h1 class="mt-3 text-xl font-medium text-gray-900">{{ bug.title }}</h1>
                        <p class="mt-4 whitespace-pre-wrap text-sm text-gray-600">{{ bug.description }}</p>
                    </div>

                    <!-- Tabs -->
                    <div class="border-t border-gray-100 pt-8">
                        <div class="flex gap-6 border-b border-gray-100">
                            <button
                                @click="activeTab = 'comments'"
                                class="pb-3 text-sm"
                                :class="activeTab === 'comments' ? 'border-b border-gray-900 text-gray-900' : 'text-gray-500'"
                            >
                                留言 ({{ bug.comments?.length || 0 }})
                            </button>
                            <button
                                @click="activeTab = 'attachments'"
                                class="pb-3 text-sm"
                                :class="activeTab === 'attachments' ? 'border-b border-gray-900 text-gray-900' : 'text-gray-500'"
                            >
                                附件 ({{ bug.attachments?.length || 0 }})
                            </button>
                            <button
                                @click="activeTab = 'activity'"
                                class="pb-3 text-sm"
                                :class="activeTab === 'activity' ? 'border-b border-gray-900 text-gray-900' : 'text-gray-500'"
                            >
                                活動
                            </button>
                        </div>

                        <div class="pt-6">
                            <!-- Comments -->
                            <div v-if="activeTab === 'comments'">
                                <form @submit.prevent="submitComment" class="mb-6">
                                    <TextArea
                                        v-model="commentForm.body"
                                        placeholder="新增留言..."
                                        :rows="3"
                                    />
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <input
                                                ref="commentFileRef"
                                                type="file"
                                                @input="commentForm.file = $event.target.files[0]"
                                                class="hidden"
                                                id="comment-file"
                                            />
                                            <label
                                                for="comment-file"
                                                class="cursor-pointer text-sm text-gray-400 hover:text-gray-900"
                                            >
                                                附加檔案
                                            </label>
                                            <span v-if="commentForm.file" class="text-xs text-gray-500">
                                                {{ commentForm.file.name }}
                                                <button
                                                    type="button"
                                                    @click="commentForm.file = null; commentFileRef.value = ''"
                                                    class="ml-1 text-gray-400 hover:text-gray-900"
                                                >×</button>
                                            </span>
                                        </div>
                                        <button
                                            type="submit"
                                            :disabled="commentForm.processing || !commentForm.body"
                                            class="text-sm text-gray-900 hover:underline disabled:opacity-50"
                                        >
                                            {{ commentForm.processing ? '發表中...' : '發表' }}
                                        </button>
                                    </div>
                                </form>

                                <div class="divide-y divide-gray-100">
                                    <div
                                        v-for="comment in bug.comments"
                                        :key="comment.id"
                                        class="py-4"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">{{ comment.user?.name }}</span>
                                            <span class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</span>
                                        </div>
                                        <p class="mt-2 whitespace-pre-wrap text-sm text-gray-600">{{ comment.body }}</p>
                                        <!-- 留言附件 -->
                                        <div v-if="comment.attachments?.length" class="mt-2">
                                            <button
                                                v-for="attachment in comment.attachments"
                                                :key="attachment.id"
                                                type="button"
                                                @click="openPreview(attachment)"
                                                class="mr-3 inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-900"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                {{ attachment.original_filename }}
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="!bug.comments?.length" class="py-8 text-center text-sm text-gray-400">
                                        尚無留言
                                    </p>
                                </div>
                            </div>

                            <!-- Attachments -->
                            <div v-else-if="activeTab === 'attachments'">
                                <!-- Upload Form -->
                                <form @submit.prevent="uploadAttachment" class="mb-6">
                                    <div class="flex items-center gap-4">
                                        <input
                                            ref="fileInputRef"
                                            type="file"
                                            @input="attachmentForm.file = $event.target.files[0]"
                                            class="text-sm text-gray-500 file:mr-3 file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm file:text-gray-700 hover:file:bg-gray-200"
                                        />
                                        <button
                                            type="submit"
                                            :disabled="!attachmentForm.file || attachmentForm.processing"
                                            class="text-sm text-gray-900 hover:underline disabled:text-gray-300 disabled:no-underline"
                                        >
                                            {{ attachmentForm.processing ? '上傳中...' : '上傳' }}
                                        </button>
                                    </div>
                                    <p v-if="attachmentForm.errors.file" class="mt-2 text-sm text-red-500">
                                        {{ attachmentForm.errors.file }}
                                    </p>
                                </form>

                                <!-- Attachment List -->
                                <div class="divide-y divide-gray-100">
                                    <button
                                        v-for="attachment in bug.attachments"
                                        :key="attachment.id"
                                        type="button"
                                        @click="openPreview(attachment)"
                                        class="flex w-full items-center justify-between py-3 text-left text-sm hover:text-gray-900"
                                    >
                                        <span class="text-gray-600">{{ attachment.original_filename }}</span>
                                        <span class="text-xs text-gray-400">{{ (attachment.size / 1024).toFixed(1) }} KB</span>
                                    </button>
                                    <p v-if="!bug.attachments?.length" class="py-8 text-center text-sm text-gray-400">
                                        尚無附件
                                    </p>
                                </div>
                            </div>

                            <!-- Activity -->
                            <div v-else-if="activeTab === 'activity'">
                                <div class="divide-y divide-gray-100">
                                    <div
                                        v-for="activity in bug.activities"
                                        :key="activity.id"
                                        class="py-4"
                                    >
                                        <div class="text-sm">
                                            <span class="font-medium text-blue-600">{{ activity.user?.name }}</span>
                                            <p class="mt-0.5 text-gray-600">{{ activityMessage(activity) }}</p>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">{{ formatDate(activity.created_at) }}</p>
                                    </div>
                                    <p v-if="!bug.activities?.length" class="py-8 text-center text-sm text-gray-400">
                                        尚無活動
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Details -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">詳細資訊</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500">回報者</dt>
                                <dd class="mt-1 text-gray-900">{{ bug.reporter?.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">負責人</dt>
                                <dd class="mt-1 text-gray-900">{{ bug.assignee?.name || '未指派' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">建立時間</dt>
                                <dd class="mt-1 text-gray-900">{{ formatDate(bug.created_at) }}</dd>
                            </div>
                            <div v-if="bug.resolved_at">
                                <dt class="text-gray-500">解決時間</dt>
                                <dd class="mt-1 text-gray-900">{{ formatDate(bug.resolved_at) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div v-if="can.update" class="border-t border-gray-100 pt-8">
                        <h3 class="text-sm font-medium text-gray-900">狀態</h3>
                        <div class="mt-3 space-y-1">
                            <button
                                v-for="status in statuses"
                                :key="status"
                                @click="updateStatus(status)"
                                :disabled="bug.status === status"
                                class="block w-full py-1.5 text-left text-sm"
                                :class="bug.status === status ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900'"
                            >
                                {{ statusLabels[status] || status }}
                            </button>
                        </div>
                    </div>

                    <!-- Edit/Delete -->
                    <div v-if="can.update" class="border-t border-gray-100 pt-8">
                        <div class="flex gap-4">
                            <Link
                                :href="route('bugs.edit', bug.id)"
                                class="text-sm text-gray-500 hover:text-gray-900"
                            >
                                編輯
                            </Link>
                            <button
                                v-if="can.delete"
                                @click="showDeleteModal = true"
                                class="text-sm text-gray-500 hover:text-red-600"
                            >
                                刪除
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" max-width="sm">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">刪除問題</h2>
                <p class="mt-2 text-sm text-gray-500">確定要刪除此問題嗎？</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="text-sm text-gray-500 hover:text-gray-900">
                        取消
                    </button>
                    <button
                        @click="deleteBug"
                        class="border border-red-600 px-3 py-1.5 text-sm text-red-600 hover:bg-red-600 hover:text-white"
                    >
                        刪除
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
