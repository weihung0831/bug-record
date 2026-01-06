<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        required: true,
    },
    placeholder: {
        type: String,
        default: '請選擇',
    },
    labelKey: {
        type: String,
        default: 'label',
    },
    valueKey: {
        type: String,
        default: 'value',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const selectedOption = computed(() => {
    return props.options.find(opt => {
        const value = typeof opt === 'object' ? opt[props.valueKey] : opt;
        return value === props.modelValue;
    });
});

const displayText = computed(() => {
    if (!selectedOption.value) return props.placeholder;
    if (typeof selectedOption.value === 'object') {
        return selectedOption.value[props.labelKey];
    }
    return selectedOption.value;
});

const getOptionValue = (option) => {
    return typeof option === 'object' ? option[props.valueKey] : option;
};

const getOptionLabel = (option) => {
    return typeof option === 'object' ? option[props.labelKey] : option;
};

const select = (option) => {
    emit('update:modelValue', getOptionValue(option));
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="flex w-full items-center justify-between border-0 bg-transparent px-0 py-2 text-left text-sm focus:outline-none"
        >
            <span :class="modelValue !== '' && modelValue !== null ? 'text-gray-900' : 'text-gray-400'">
                {{ displayText }}
            </span>
            <svg
                class="h-4 w-4 text-gray-400 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-auto border border-gray-200 bg-white py-1"
            >
                <button
                    v-for="option in options"
                    :key="getOptionValue(option)"
                    type="button"
                    @click="select(option)"
                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50"
                    :class="getOptionValue(option) === modelValue ? 'text-gray-900' : 'text-gray-600'"
                >
                    {{ getOptionLabel(option) }}
                </button>
            </div>
        </Transition>
    </div>
</template>
