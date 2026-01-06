<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

defineProps({
    rows: {
        type: [String, Number],
        default: 4,
    },
    placeholder: {
        type: String,
        default: '',
    },
});

const textarea = ref(null);

onMounted(() => {
    if (textarea.value.hasAttribute('autofocus')) {
        textarea.value.focus();
    }
});

defineExpose({ focus: () => textarea.value.focus() });
</script>

<template>
    <textarea
        class="w-full resize-none border-0 bg-transparent px-0 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0"
        v-model="model"
        ref="textarea"
        :rows="rows"
        :placeholder="placeholder"
    />
</template>
