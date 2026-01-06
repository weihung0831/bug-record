<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:border-gray-900 hover:text-gray-900 disabled:opacity-50"
    >
        <slot />
    </button>
</template>
