<script setup>
import { ref } from 'vue';
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
    lost_statuses: Object,
});

const form = useForm({
    year: 2025,
    status: null,
    start_date: '',
    end_date: ''
});

const loading = ref(false);

// Observa cambios en las fechas para validar
watch([() => form.start_date, () => form.end_date], ([start, end]) => {
    if (start && end && start > end) {
        toast.warning('La fecha de inicio no puede ser mayor a la fecha de fin');
        form.start_date = '';
    }
});

const submit = () => {
    loading.value = true;

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
        toast.success('Reporte generado');
    })
    .catch(error => {
        console.error('Error downloading the file:', error);
        toast.error('Error al obtener los datos');
    })
    .finally(() => {
        loading.value = false;
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
                                    <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" viewBox="0 0 24 24"><path fill="currentColor" d="m12 16l-5-5l1.4-1.45l2.6 2.6V4h2v8.15l2.6-2.6L17 11zm-6 4q-.825 0-1.412-.587T4 18v-3h2v3h12v-3h2v3q0 .825-.587 1.413T18 20z"/></svg>

                                    <svg v-if="loading" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path stroke-dasharray="16" stroke-dashoffset="16" d="M12 3c4.97 0 9 4.03 9 9"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.3s" values="16;0"/><animateTransform attributeName="transform" dur="1.5s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path><path stroke-dasharray="64" stroke-dashoffset="64" stroke-opacity=".3" d="M12 3c4.97 0 9 4.03 9 9c0 4.97 -4.03 9 -9 9c-4.97 0 -9 -4.03 -9 -9c0 -4.97 4.03 -9 9 -9Z"><animate fill="freeze" attributeName="stroke-dashoffset" dur="1.2s" values="64;0"/></path></g></svg>
                                    Generar reporte
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
