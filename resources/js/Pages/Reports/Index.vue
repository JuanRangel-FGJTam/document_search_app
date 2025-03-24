<script setup>
import { onMounted } from 'vue';
import { ref } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import ModalConfirmation from '@/Components/ModalConfirmation.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';

const toast = useToast();
const props = defineProps({
    errors: Object,
    years: Array,
    lost_statuses: Object
});

const form = useForm({
    year: 2025,
    status: null
});


const submit = () => {
    if (!form.year) {
        toast.warning('Ingrese un año');
        return;
    }
    toast.info('Generando reporte...');
    axios.post(route('reports.getByYear'), form, {
        responseType: 'blob'
    })
        .then(response => {
            // Crear una URL para el blob
            const url = window.URL.createObjectURL(new Blob([response.data]));

            // Crear un elemento de enlace y activar la descarga
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'Solicitudes_Constancias_' + form.year + '.xlsx');  // Establecer el nombre de archivo

            // Agregar al documento y activar el clic
            document.body.appendChild(link);
            link.click();

            // Limpiar
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            toast.success('Reporte generado con éxito');
        })
        .catch(error => {
            console.error('Error downloading the file:', error);
            toast.error('Error al obtener los datos');
        });
}

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
                        <form @submit.prevent="submit">
                            <div class="grid gap-4 mb-4 sm:grid-cols-2 sm:gap-6 sm:mb-5">
                                <div class="col-span-1">
                                    <label for="deadline"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Seleccione año a exportar
                                    </label>
                                    <select id="year" name="year" v-model="form.year"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        <option disabled value="">Seleccione un elemento</option>
                                        <option v-for="year in years" :key="year" v-bind:value="year">
                                            {{ year }}
                                        </option>
                                    </select>
                                    <InputError v-if="errors.year" :message="errors.year" />
                                </div>
                                <div class="col-span-1">
                                    <label for="deadline"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Seleccione status de solicitudes
                                    </label>
                                    <select id="status" name="status" v-model="form.status"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        <option disabled value="">Seleccione un elemento</option>
                                        <option :value="null">TODOS</option>
                                        <option v-for="lost_status in lost_statuses" :key="lost_status.id" v-bind:value="lost_status.id">
                                            {{ lost_status.name }}
                                        </option>
                                    </select>
                                    <InputError v-if="errors.status" :message="errors.status" />
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
