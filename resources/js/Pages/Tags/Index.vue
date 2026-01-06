<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    tags: Array,
});

const showCreateModal = ref(false);
const showDeleteModal = ref(false);
const editingTag = ref(null);
const deletingTag = ref(null);

const form = useForm({
    name: '',
    color: '#6366f1',
});

const openCreate = () => {
    form.reset();
    editingTag.value = null;
    showCreateModal.value = true;
};

const openEdit = (tag) => {
    form.name = tag.name;
    form.color = tag.color;
    editingTag.value = tag;
    showCreateModal.value = true;
};

const openDelete = (tag) => {
    deletingTag.value = tag;
    showDeleteModal.value = true;
};

const submit = () => {
    if (editingTag.value) {
        form.put(route('tags.update', editingTag.value.id), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('tags.store'), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteTag = () => {
    router.delete(route('tags.destroy', deletingTag.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingTag.value = null;
        },
    });
};

const predefinedColors = [
    '#ef4444', '#f97316', '#eab308', '#22c55e',
    '#14b8a6', '#3b82f6', '#6366f1', '#8b5cf6',
    '#ec4899', '#6b7280',
];
</script>

<template>
    <Head title="標籤" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">標籤</h2>
                <button
                    @click="openCreate"
                    class="border border-gray-900 px-3 py-1.5 text-sm text-gray-900 hover:bg-gray-900 hover:text-white"
                >
                    新增
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="divide-y divide-gray-100">
                <div
                    v-for="tag in tags"
                    :key="tag.id"
                    class="flex items-center justify-between py-4"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-block h-3 w-3 rounded-full"
                            :style="{ backgroundColor: tag.color }"
                        />
                        <span class="text-sm text-gray-900">{{ tag.name }}</span>
                        <span class="text-xs text-gray-400">{{ tag.bugs_count }} 個問題</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click="openEdit(tag)" class="text-xs text-gray-500 hover:text-gray-900">
                            編輯
                        </button>
                        <button @click="openDelete(tag)" class="text-xs text-gray-500 hover:text-red-600">
                            刪除
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!tags.length" class="py-16 text-center">
                <p class="text-sm text-gray-400">尚無標籤</p>
                <button
                    @click="openCreate"
                    class="mt-4 text-sm text-gray-900 hover:underline"
                >
                    建立第一個標籤
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="sm">
            <form @submit.prevent="submit" class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingTag ? '編輯標籤' : '新增標籤' }}
                </h2>

                <div class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="name" value="名稱" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="顏色" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="color in predefinedColors"
                                :key="color"
                                type="button"
                                @click="form.color = color"
                                class="h-6 w-6 rounded-full"
                                :class="form.color === color ? 'ring-2 ring-gray-900 ring-offset-2' : ''"
                                :style="{ backgroundColor: color }"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">預覽：</span>
                        <span
                            class="inline-block h-3 w-3 rounded-full"
                            :style="{ backgroundColor: form.color }"
                        />
                        <span class="text-sm">{{ form.name || '標籤名稱' }}</span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="text-sm text-gray-500 hover:text-gray-900"
                    >
                        取消
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="border border-gray-900 px-3 py-1.5 text-sm text-gray-900 hover:bg-gray-900 hover:text-white disabled:opacity-50"
                    >
                        {{ editingTag ? '儲存' : '建立' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" max-width="sm">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">刪除標籤</h2>
                <p class="mt-2 text-sm text-gray-500">
                    確定要刪除「{{ deletingTag?.name }}」嗎？
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="text-sm text-gray-500 hover:text-gray-900"
                    >
                        取消
                    </button>
                    <button
                        @click="deleteTag"
                        class="border border-red-600 px-3 py-1.5 text-sm text-red-600 hover:bg-red-600 hover:text-white"
                    >
                        刪除
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
