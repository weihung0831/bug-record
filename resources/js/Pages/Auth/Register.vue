<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="註冊" />

    <div class="flex min-h-screen items-center justify-center bg-white px-4">
        <div class="w-full max-w-sm">
            <h1 class="text-center text-2xl font-semibold text-gray-900">註冊</h1>

            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div>
                    <InputLabel for="name" value="名稱" />
                    <div class="mt-1 border-b border-gray-200 focus-within:border-gray-900 transition-colors">
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full"
                            v-model="form.name"
                            required
                            autofocus
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="電子郵件" />
                    <div class="mt-1 border-b border-gray-200 focus-within:border-gray-900 transition-colors">
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full"
                            v-model="form.email"
                            required
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

                <div>
                    <InputLabel for="password_confirmation" value="確認密碼" />
                    <div class="mt-1 border-b border-gray-200 focus-within:border-gray-900 transition-colors">
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full"
                            v-model="form.password_confirmation"
                            required
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <PrimaryButton
                    class="w-full justify-center"
                    :disabled="form.processing"
                >
                    註冊
                </PrimaryButton>

                <p class="text-center text-sm text-gray-600">
                    已有帳號？
                    <Link :href="route('login')" class="text-gray-900 hover:underline">
                        登入
                    </Link>
                </p>
            </form>
        </div>
    </div>
</template>
