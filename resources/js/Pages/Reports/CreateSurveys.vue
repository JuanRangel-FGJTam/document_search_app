<script setup>
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import BackButton from '@/Components/BackButton.vue';

const toast = useToast();

const props = defineProps({
    errors: Object,
    years: Array,
    lost_statuses: Object
});

const form = useForm({
    year: 2025,
    status: null,
    start_date: '',
    end_date: ''
});

// Observa cambios en las fechas para validar
watch([() => form.start_date, () => form.end_date], ([start, end]) => {
    if (start && end && start > end) {
        toast.warning('La fecha de inicio no puede ser mayor a la fecha de fin');
        form.start_date = '';
    }
});

const submit = () => {
    if (!form.year) {
        toast.warning('Ingrese un año');
        return;
    }
    if (!form.start_date || !form.end_date) {
        toast.warning('Debe seleccionar ambas fechas');
        return;
    }
    toast.info('Generando reporte...');
    axios.post(route('reports.getSurveys'), form, {
        responseType: 'blob'
    })
        .then(response => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `Reporte de encuestas de ${form.start_date} al ${form.end_date}.xlsx`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            toast.success('Reporte generado con éxito');
        })
        .catch(error => {
            console.error('Error downloading the file:', error);
            toast.error('Error al obtener los datos');
        });
};
</script>


<template>
    <AppLayout title="Reportes de constancias">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="w-full px-2 py-2">
                        <div class="flex items-center justify-start mb-4">
                            <BackButton :href="route('reports.index')" />
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                Reportes de encuestas
                            </h2>
                        </div>
                        <form @submit.prevent="submit">
                            <div class="grid gap-4 mb-4 sm:grid-cols-2 sm:gap-6 sm:mb-5">
                                <div class="col-span-1">
                                    <label for="start_date"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Seleccione fecha de inicio
                                    </label>
                                    <input type="date" id="start_date" v-model="form.start_date"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
                                    <InputError v-if="errors.start_date" :message="errors.start_date" />
                                </div>
                                <div class="col-span-1">
                                    <label for="end_date"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Seleccione fecha de fin
                                    </label>
                                    <input type="date" id="end_date" v-model="form.end_date"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
                                    <InputError v-if="errors.end_date" :message="errors.end_date" />
                                </div>
                            </div>
                            <div class="flex items-center justify-end space-x-4">
                                <button type="submit"
                                    class="flex items-center px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-150 ease-in-out text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-6 h-6 mr-2"
                                        fill="currentColor">
                                        <path d="M18.9 35.7 7.7 24.5 9.85 22.35 18.9 31.4 38.1 12.2 40.25 14.35Z" />
                                    </svg>
                                    Exportar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
