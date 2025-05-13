<script setup>
import { defineProps, defineEmits, ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    header: {
        type: String,
        default: 'Modal Header',
    },
});

const emit = defineEmits(['close', 'save']); // Agregamos 'save' como evento emitido al presionar el botón "Guardar"

watch(() => props.show, () => {
    if (props.show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = null;
    }
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const save = () => {
    emit('save'); // Emitimos el evento 'save' al presionar el botón "Guardar"
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
        '3xl': 'sm:max-w-7xl',
    }[props.maxWidth];
});
</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="show" class="fixed inset-0 flex items-center justify-center px-4 py-6 sm:px-0 z-50"
                scroll-region>
                <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                    enter-to-class="opacity-100" leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-show="show" class="fixed inset-0 transform transition-all" @click="close">
                        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75" />
                    </div>
                </transition>

                <transition enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div v-show="show"
                        class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
                        :class="maxWidthClass">
                        <!-- Header -->
                        <div class="bg-gray-200 dark:bg-gray-700 p-4">
                            <h3 class="text-lg font-semibold dark:text-white">
                                <slot name="icon">
                                </slot>
                                {{ header }}
                            </h3>
                        </div>

                        <!-- Contenido del modal -->
                        <div class="p-6">
                            <slot v-if="show" />
                        </div>

                        <!-- Footer con botones -->
                        <div class="bg-gray-200 dark:bg-gray-700 p-4 flex justify-between">
                            <button class="bg-red-500 text-white px-4 py-2 rounded" @click="close">Cancelar</button>
                            <button @click="save" class="bg-blue-500 text-white px-4 py-2 rounded">Guardar</button>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>
