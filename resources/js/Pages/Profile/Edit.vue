<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="個人資料" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-medium text-gray-900">
                個人資料
            </h2>
        </template>

        <div class="py-8">
            <div class="space-y-8">
                <div class="border-b border-gray-100 pb-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="border-b border-gray-100 pb-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div>
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
