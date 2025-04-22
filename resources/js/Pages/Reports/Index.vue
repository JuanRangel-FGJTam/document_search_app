<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    errors: Object,
    report_types: Object
});
</script>

<template>
    <AppLayout title="Reportes de constancias">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="w-full px-4 py-4">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                            Reportes de solicitudes de constancias
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-8">
                            <!-- Reporte por Año -->
                            <Link
                                class="flex items-center justify-between p-6 rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-300 ease-in-out cursor-pointer"
                                :href="route('reports.createByYear')">
                            <div class="flex items-center">
                                <div class="text-3xl text-indigo-600 mr-6">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="text-lg font-semibold text-gray-800">
                                    Reportes y gráficas de constancias de extravio
                                </div>
                            </div>
                            <div class="text-indigo-500">
                                <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" height="24px"
                                    viewBox="0 -960 960 960" width="24px" fill="currentColor">
                                    <path
                                        d="M320-440h320v-80H320v80Zm0 120h320v-80H320v80Zm0 120h200v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z" />
                                </svg>
                            </div>
                            </Link>

                            <!-- Reporte de Encuestas -->
                            <Link
                                class="flex items-center justify-between p-6 rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition duration-300 ease-in-out cursor-pointer"
                                :href="route('reports.createSurveys')">
                            <div class="flex items-center">
                                <div class="text-lg font-semibold text-gray-800">
                                    Reporte de encuestas de satisfacción
                                </div>
                            </div>
                            <div class="text-green-500">
                                <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                    width="24px" fill="currentColor">
                                    <path
                                        d="M480-260q68 0 123.5-38.5T684-400H276q25 63 80.5 101.5T480-260ZM312-520l44-42 42 42 42-42-84-86-86 86 42 42Zm250 0 42-42 44 42 42-42-86-86-84 86 42 42ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-400Zm0 320q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Z" />
                                </svg>
                            </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Estilos personalizados */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

.transition {
    transition: all 0.3s ease-in-out;
}
</style>
