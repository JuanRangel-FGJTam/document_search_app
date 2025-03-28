<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import BackButton from '@/Components/BackButton.vue';
import { watch } from 'vue';
const toast = useToast();
const props = defineProps({
    errors: Object,
    years: Array,
    lost_statuses: Object,
    municipalities: Array,
    report_types: Object
});

const form = useForm({
    reportType: 1, // 1: Por Año, 2: Por Días, 3: Municipio por Días, 4: Por Municipio
    year: 2025,
    status: null,
    start_date: null,
    end_date: null,
    municipality: null,
});

watch(() => form.reportType, (newValue) => {
    if ([2, 3].includes(newValue)) {
        // Observa cambios en las fechas para validar
        watch([() => form.start_date, () => form.end_date], ([start, end]) => {
            if (start && end && start > end) {
                toast.warning('La fecha de inicio no puede ser mayor a la fecha de fin');
                form.start_date = '';
            }
        });
    }

    if ([3, 4].includes(newValue)) {
        router.get(route('reports.createByYear'), {
            municipality: newValue,
        },
            {
                preserveState: true,
                only: ['municipalities'],
            });
    }
});

const submit = () => {
    if ((form.reportType === 1 || form.reportType === 4) && !form.year) {
        toast.warning('Ingrese un año');
        return;
    }
    if ((form.reportType === 2 || form.reportType === 3) && (!form.startDate || !form.endDate)) {
        toast.warning('Seleccione una fecha de inicio y fin');
        return;
    }
    if ((form.reportType === 3 || form.reportType === 4) && !form.municipality) {
        toast.warning('Seleccione un municipio');
        return;
    }

    toast.info('Generando reporte...');

    axios.post(route('reports.generate'), form, { responseType: 'blob' })
        .then(response => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            const reportTypeName = props.report_types.find(type => type.id === form.reportType)?.name || 'Reporte';
            link.setAttribute('download', `Reporte_Constancias_${reportTypeName}.xlsx`);
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
                                Reportes de solicitudes de constancias
                            </h2>
                        </div>
                        <form @submit.prevent="submit">
                            <div class="grid gap-4 mb-4 sm:grid-cols-2 sm:gap-6 sm:mb-5">

                                <!-- Seleccionar tipo de reporte -->
                                <div class="col-span-2">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Tipo de Reporte
                                    </label>
                                    <select v-model="form.reportType"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                                        <option v-for="(label, key) in report_types" :key="label.id" :value="label.id">
                                            {{ label.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Selección de año (solo si el reporte es por año) -->
                                <div class="col-span-1" v-if="[1, 4].includes(form.reportType)">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Seleccione Año
                                    </label>
                                    <select v-model="form.year"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                                        <option disabled value="">Seleccione un año</option>
                                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>

                                <!-- Selección de fecha (para reportes por días o municipio y días) -->
                                <div v-if="[2, 3].includes(form.reportType)" class="col-span-1">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Fecha de Inicio
                                    </label>
                                    <input type="date" v-model="form.start_date"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                                </div>
                                <div v-if="[2, 3].includes(form.reportType)" class="col-span-1">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Fecha de Fin
                                    </label>
                                    <input type="date" v-model="form.end_date"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                                </div>

                                <!-- Selección de Municipio (para reportes por municipio y municipio por días) -->
                                <div v-if="[3, 4].includes(form.reportType)" class="col-span-1">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Seleccione Municipio
                                    </label>
                                    <select v-model="form.municipality"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                                        <option disabled :value="null">Seleccione un municipio</option>
                                        <option v-for="municipality in municipalities" :key="municipality.id"
                                            :value="municipality.id">
                                            {{ municipality.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Selección de Status (opcional en todos los reportes) -->
                                <div class="col-span-1">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Estado de Solicitudes
                                    </label>
                                    <select v-model="form.status"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                                        <option :value="null">TODOS</option>
                                        <option v-for="lost_status in lost_statuses" :key="lost_status.id"
                                            :value="lost_status.id">
                                            {{ lost_status.name }}
                                        </option>
                                    </select>
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
