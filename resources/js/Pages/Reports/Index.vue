<script setup>
import { Link, router } from '@inertiajs/vue3';
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
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="w-full px-2 py-2">
                        <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">
                            Reportes de solicitudes de constancias
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            <Link
                                class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 cursor-pointer"
                                :href="route('reports.createByYear')"
                            >
                                <div class="flex items-center">
                                    <div class="text-2xl text-gray-600 mr-4">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="text-lg font-semibold text-gray-800">
                                        Reporte de constancias por año
                                    </div>
                                </div>
                                <div class="text-gray-500">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </Link>
                            <Link
                                class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 cursor-pointer"
                                :href="route('reports.createSurveys')">
                                <div class="flex items-center">
                                    <div class="text-2xl text-gray-600 mr-4">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div class="text-lg font-semibold text-gray-800">
                                        Reporte de encuestas
                                    </div>
                                </div>
                                <div class="text-gray-500">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </Link>
                            <div
                                class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 cursor-pointer"
                                @click="router.visit('/report3')"
                            >
                                <div class="flex items-center">
                                    <div class="text-2xl text-gray-600 mr-4">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div class="text-lg font-semibold text-gray-800">
                                        Reporte 3
                                    </div>
                                </div>
                                <div class="text-gray-500">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
