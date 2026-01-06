<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="登入" />

    <div class="flex min-h-screen items-center justify-center bg-white px-4">
        <div class="w-full max-w-sm">
            <h1 class="text-center text-2xl font-semibold text-gray-900">登入</h1>

            <div v-if="status" class="mt-4 text-center text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div>
                    <InputLabel for="email" value="電子郵件" />
                    <div class="mt-1 border-b border-gray-200 focus-within:border-gray-900 transition-colors">
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full"
                            v-model="form.email"
                            required
                            autofocus
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="密碼" />
                    <div class="mt-1 border-b border-gray-200 focus-within:border-gray-900 transition-colors">
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full"
                            v-model="form.password"
                            required
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ml-2 text-sm text-gray-600">記住我</span>
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-gray-600 hover:underline"
                    >
                        忘記密碼
                    </Link>
                </div>

                <PrimaryButton
                    class="w-full justify-center"
                    :disabled="form.processing"
                >
                    登入
                </PrimaryButton>

                <p class="text-center text-sm text-gray-600">
                    沒有帳號？
                    <Link :href="route('register')" class="text-gray-900 hover:underline">
                        註冊
                    </Link>
                </p>
            </form>
        </div>
    </div>
</template>
