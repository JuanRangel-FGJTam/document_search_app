<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { watch, ref } from 'vue';
import { Doughnut, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement
} from 'chart.js';

const barChartData = ref(null)
const doughnutData = ref(null)
// Registrar los componentes de Chart.js
ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement
)
// Datos del gráfico
const barChartOptions = {
    responsive: true,
    plugins: {
        legend: { display: false },
        title: { display: true, text: 'Solicitudes por mes' },
    }
}

const doughnutOptions = {
    responsive: true,
    plugins: {
        legend: { position: 'bottom' },
        title: { display: true, text: 'Identificaciones usadas' },
    }
}

const toast = useToast();
const props = defineProps({
    errors: Object,
    years: Array,
    lost_statuses: Object,
    municipalities: Array,
    report_types: Object,
    document_types: Object,
});
const loadingXLSX = ref(false);
const loadingChart = ref(false);
const form = useForm({
    reportType: 1, // 1: Por Año, 2: Por Días, 3: Municipio por Días, 4: Por Municipio
    year: 2025,
    status: null,
    start_date: null,
    end_date: null,
    municipality: null,
    document_type: null,
    keyword: null,
    download: false,
});

watch(() => form.reportType, (newValue) => {
    // Reiniciar valores al cambiar el tipo de reporte
    Object.assign(form, {
        year: 2025,
        start_date: null,
        end_date: null,
        municipality: null,
        document_type: null,
        keyword: null,
    });
    [barChartData, doughnutData, loadingXLSX, loadingChart].forEach(refVar => refVar.value = null);
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

const validateForm = () => {
    if ((form.reportType === 1 || form.reportType === 4 || form.reportType == 6) && !form.year) {
        toast.warning('Ingrese un año');
        return false;
    }
    if ((form.reportType === 2 || form.reportType === 3) && !form.start_date && !form.end_date) {
        toast.warning('Seleccione al menos una fecha.');
        return false;
    }
    return true;
};

const downloadReport = async () => {
    loadingXLSX.value = true;

    if (!validateForm()) {
        loadingXLSX.value = false;
        return;
    }

    try {
        toast.info('Generando reporte...');
        form.download = true;

        const response = await axios.post(route('reports.generate'), form, { responseType: 'blob' });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        const reportTypeName = props.report_types.find(type => type.id === form.reportType)?.name || 'Reporte';

        link.href = url;
        link.setAttribute('download', `Reporte_Constancias_${reportTypeName}.xlsx`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        toast.success('Reporte generado con éxito');
    } catch (error) {
        console.error('Error downloading the file:', error);
        toast.error('Error al obtener los datos');
    } finally {
        loadingXLSX.value = false;
    }
};

const generateChart = async () => {
    loadingChart.value = true;

    if (!validateForm()) {
        loadingChart.value = false;
        return;
    }

    try {
        toast.info('Generando gráfico...');
        form.download = false;

        const { data } = await axios.post(route('reports.generate'), form);
        console.log('Data for chart:', data);
        const perMonth = data.totalPerMonth || {};
        const perIdentification = data.totalPerIdentification || {};

        barChartData.value = {
            labels: Object.keys(perMonth),
            datasets: [
                {
                    label: 'Solicitudes por mes',
                    data: Object.values(perMonth),
                    backgroundColor: '#42A5F5',
                },
            ],
        };

        doughnutData.value = {
            labels: Object.keys(perIdentification),
            datasets: [
                {
                    label: 'Tipo de identificación',
                    data: Object.values(perIdentification),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8BC34A', '#9C27B0'],
                },
            ],
        };
        toast.success('Gráficas generadas');
    } catch (error) {
        console.error('Error to make chart:', error);
        toast.error('Error al obtener los datos');
    } finally {
        loadingChart.value = false;
    }
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
                                <div class="col-span-1" v-if="[1, 4, 6].includes(form.reportType)">
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
                                <div class="col-span-1"
                                    v-if="[2, 3].includes(form.reportType) && form.document_type === 9">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Palabra Clave
                                    </label>
                                    <input type="text" v-model="form.keyword"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5"
                                        placeholder="Ingrese una palabra clave">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-if="barChartData"
                                    class="w-full h-80 p-4 bg-white rounded-xl shadow overflow-hidden">
                                    <Bar :data="barChartData" :options="barChartOptions" class="w-full h-full" />
                                </div>
                                <div v-if="doughnutData"
                                    class="w-full h-80 p-4 bg-white rounded-xl shadow overflow-hidden flex justify-center">
                                    <Doughnut :data="doughnutData" :options="doughnutOptions" class="w-full h-full" />
                                </div>
                            </div>
                            <div class="flex items-center justify-end space-x-4 mt-4">
                                <button type="button" @click="generateChart"
                                    class="flex items-center px-5 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-150 ease-in-out text-sm">
                                    <svg v-if="!loadingChart" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2"
                                        height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                                        <path
                                            d="M480-80q-83 0-156-31.5t-127-86Q143-252 111.5-325T80-480q0-157 104-270t256-128v120q-103 14-171.5 92.5T200-480q0 116 82 198t198 82q66 0 123.5-28t96.5-76l104 60q-54 75-139 119.5T480-80Zm366-238-104-60q9-24 13.5-49.5T760-480q0-107-68.5-185.5T520-758v-120q152 15 256 128t104 270q0 44-8 85t-26 77Z" />
                                    </svg>
                                    <svg v-if="loadingChart" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2"
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
                                    Graficar
                                </button>
                                <button type="button" @click="downloadReport"
                                    class="flex items-center px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-150 ease-in-out text-sm">
                                    <svg v-if="!loadingXLSX" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="m12 16l-5-5l1.4-1.45l2.6 2.6V4h2v8.15l2.6-2.6L17 11zm-6 4q-.825 0-1.412-.587T4 18v-3h2v3h12v-3h2v3q0 .825-.587 1.413T18 20z" />
                                    </svg>

                                    <svg v-if="loadingXLSX" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2"
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
                                    Exportar XLSX
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
