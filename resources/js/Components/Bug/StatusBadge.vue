<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: String,
});

const statusConfig = {
    open: { label: '開放', color: '#ca8a04' },
    in_progress: { label: '進行中', color: '#2563eb' },
    resolved: { label: '已解決', color: '#16a34a' },
    closed: { label: '已關閉', color: '#6b7280' },
};

const config = computed(() => statusConfig[props.status] || statusConfig.open);
</script>

<template>
    <span
        class="inline-flex items-center text-xs"
        :style="{ color: config.color }"
    >
        {{ config.label }}
    </span>
</template>
