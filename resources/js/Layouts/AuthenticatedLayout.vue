<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="border-b border-gray-100">
            <div class="mx-auto max-w-5xl px-6">
                <div class="flex h-14 items-center justify-between">
                    <!-- Logo & Nav -->
                    <div class="flex items-center gap-8">
                        <Link :href="route('dashboard')" class="text-sm font-medium text-gray-900">
                            Bug Record
                        </Link>
                        <div class="hidden items-center gap-6 sm:flex">
                            <Link
                                :href="route('dashboard')"
                                class="text-sm text-gray-500 hover:text-gray-900"
                                :class="{ 'text-gray-900': route().current('dashboard') }"
                            >
                                儀表板
                            </Link>
                            <Link
                                :href="route('bugs.index')"
                                class="text-sm text-gray-500 hover:text-gray-900"
                                :class="{ 'text-gray-900': route().current('bugs.*') }"
                            >
                                問題
                            </Link>
                            <Link
                                :href="route('tags.index')"
                                class="text-sm text-gray-500 hover:text-gray-900"
                                :class="{ 'text-gray-900': route().current('tags.*') }"
                            >
                                標籤
                            </Link>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="hidden sm:block">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="text-sm text-gray-500 hover:text-gray-900">
                                    {{ $page.props.auth.user.name }}
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    個人資料
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    登出
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="sm:hidden"
                    >
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                v-if="!showingNavigationDropdown"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-show="showingNavigationDropdown" class="border-t border-gray-100 sm:hidden">
                <div class="space-y-1 px-6 py-3">
                    <Link
                        :href="route('dashboard')"
                        class="block py-2 text-sm text-gray-500"
                        :class="{ 'text-gray-900': route().current('dashboard') }"
                    >
                        儀表板
                    </Link>
                    <Link
                        :href="route('bugs.index')"
                        class="block py-2 text-sm text-gray-500"
                        :class="{ 'text-gray-900': route().current('bugs.*') }"
                    >
                        問題
                    </Link>
                    <Link
                        :href="route('tags.index')"
                        class="block py-2 text-sm text-gray-500"
                        :class="{ 'text-gray-900': route().current('tags.*') }"
                    >
                        標籤
                    </Link>
                </div>
                <div class="border-t border-gray-100 px-6 py-3">
                    <div class="text-sm text-gray-900">{{ $page.props.auth.user.name }}</div>
                    <div class="mt-2 space-y-1">
                        <Link :href="route('profile.edit')" class="block py-1 text-sm text-gray-500">
                            個人資料
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="block py-1 text-sm text-gray-500">
                            登出
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header v-if="$slots.header" class="border-b border-gray-100">
            <div class="mx-auto max-w-5xl px-6 py-6">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <div class="mx-auto max-w-5xl px-6">
                <slot />
            </div>
        </main>
    </div>
</template>
