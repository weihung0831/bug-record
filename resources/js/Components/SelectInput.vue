<script setup>
import { computed } from 'vue';
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: '',
    },
    options: {
        type: Array,
        required: true,
    },
    placeholder: {
        type: String,
        default: '請選擇',
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue);
});

const updateValue = (option) => {
    emit('update:modelValue', option.value);
};
</script>

<template>
    <Listbox :model-value="selectedOption" @update:model-value="updateValue">
        <div class="relative">
            <ListboxButton
                class="relative w-full cursor-pointer rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-left text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            >
                <span :class="selectedOption ? 'text-gray-900' : 'text-gray-500'">
                    {{ selectedOption?.label || placeholder }}
                </span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </span>
            </ListboxButton>

            <transition
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <ListboxOptions
                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 text-sm shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                >
                    <ListboxOption
                        v-for="option in options"
                        :key="option.value"
                        :value="option"
                        v-slot="{ active, selected }"
                        as="template"
                    >
                        <li
                            :class="[
                                active ? 'bg-indigo-50 text-indigo-900' : 'text-gray-900',
                                'relative cursor-pointer select-none py-2 pl-10 pr-4',
                            ]"
                        >
                            <span :class="[selected ? 'font-semibold' : 'font-normal']">
                                {{ option.label }}
                            </span>
                            <span
                                v-if="selected"
                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>
