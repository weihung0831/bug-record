<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full border-0 bg-transparent px-0 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0"
        v-model="model"
        ref="input"
    />
</template>
