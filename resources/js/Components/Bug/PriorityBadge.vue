<script setup>
import { computed } from 'vue';

const props = defineProps({
    priority: String,
});

const priorityConfig = {
    critical: { label: '緊急', color: '#dc2626' },
    high: { label: '高', color: '#ea580c' },
    medium: { label: '中', color: '#ca8a04' },
    low: { label: '低', color: '#6b7280' },
};

const config = computed(() => priorityConfig[props.priority] || priorityConfig.medium);
</script>

<template>
    <span
        class="inline-flex items-center text-xs"
        :style="{ color: config.color }"
    >
        {{ config.label }}
    </span>
</template>
