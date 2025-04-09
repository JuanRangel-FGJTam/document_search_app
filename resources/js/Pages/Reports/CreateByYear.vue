<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import BackButton from '@/Components/BackButton.vue';
import { watch, ref } from 'vue';
const toast = useToast();
const props = defineProps({
    errors: Object,
    years: Array,
    lost_statuses: Object,
    municipalities: Array,
    report_types: Object,
    document_types: Object,
});
const loading = ref(false);

const form = useForm({
    reportType: 2, // 1: Por Año, 2: Por Días, 3: Municipio por Días, 4: Por Municipio
    year: 2025,
    status: null,
    start_date: null,
    end_date: null,
    municipality: null,
    document_type: null,
    keyword: null,
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
    loading.value = true;
    if ((form.reportType === 1 || form.reportType === 4) && !form.year) {
        toast.warning('Ingrese un año');
        loading.value = false;
        return;
    }
    if ((form.reportType === 2 || form.reportType === 3) && !form.start_date && !form.end_date) {
        toast.warning('Seleccione al menos una fecha.');
        loading.value = false;
        return;
    }
    if ((form.reportType === 3 || form.reportType === 4) && !form.municipality) {
        toast.warning('Seleccione un municipio');
        loading.value = false;
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
        }).finally(() => {
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
                                <div class="col-span-1" v-if="[2, 3].includes(form.reportType)">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Tipo de Documento extraviado
                                    </label>
                                    <select v-model="form.document_type"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                                        <option :value="null">Seleccione una opción</option>
                                        <option v-for="document in document_types" :key="document.id"
                                            :value="document.id">
                                            {{ document.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-span-1" v-if="[2, 3].includes(form.reportType) && form.document_type === 5">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Palabra Clave
                                    </label>
                                    <input type="text" v-model="form.keyword"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5"
                                        placeholder="Ingrese una palabra clave">
                                </div>
                            </div>
                            <div class="flex items-center justify-end space-x-4">
                                <button type="submit"
                                    class="flex items-center px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-150 ease-in-out text-sm">
                                    <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="m12 16l-5-5l1.4-1.45l2.6 2.6V4h2v8.15l2.6-2.6L17 11zm-6 4q-.825 0-1.412-.587T4 18v-3h2v3h12v-3h2v3q0 .825-.587 1.413T18 20z" />
                                    </svg>

                                    <svg v-if="loading" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2"
                                        viewBox="0 0 24 24">
                                        <g fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="2">
                                            <path stroke-dasharray="16" stroke-dashoffset="16"
                                                d="M12 3c4.97 0 9 4.03 9 9">
                                                <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.3s"
                                                    values="16;0" />
                                                <animateTransform attributeName="transform" dur="1.5s"
                                                    repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12" />
                                            </path>
                                            <path stroke-dasharray="64" stroke-dashoffset="64" stroke-opacity=".3"
                                                d="M12 3c4.97 0 9 4.03 9 9c0 4.97 -4.03 9 -9 9c-4.97 0 -9 -4.03 -9 -9c0 -4.97 4.03 -9 9 -9Z">
                                                <animate fill="freeze" attributeName="stroke-dashoffset" dur="1.2s"
                                                    values="64;0" />
                                            </path>
                                        </g>
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
