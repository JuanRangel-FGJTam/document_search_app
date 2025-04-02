<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';
import { useToast } from 'vue-toastification';
const props = defineProps({
    surveys: {
        type: Object,
    },
    totalSurveys: {
        type: Number
    },
    years: Array,
    months: Object,
    currentYear: String,
    currentMonth: String
});

const toast = useToast();
const selectYear = ref(props.currentYear);
const selectMonth = ref(props.currentMonth);
const onChangeDate = () => {
    const year = selectYear.value;
    const month = selectMonth.value;

    if (year == null) {
        toast.warning('Se debe de seleccionar una año en específico.');
        return;
    }

    if (month == null) {
        toast.warning('Se debe de seleccionar un mes en específico');
        return;
    }
    router.get(route('surveys.index'), {
        year,
        month
    }, {
        preserveState: true,
        only: ['surveys', 'totalSurveys', 'months'],
        onSuccess: (page) => {

        },
        onError: () => {
            console.log('Error');
        }
    });
};

</script>

<template>
    <AppLayout title="Solicitudes Constancias">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- component -->
                    <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Encabezado y contador -->
                        <div class="flex items-center gap-x-3">
                            <h2 class="text-lg font-semibold leading-tight text-gray-800 uppercase dark:text-gray-200">
                                Encuestas de Constancias de Extravio de Documentos
                            </h2>
                            <span
                                class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full dark:bg-gray-800 dark:text-blue-400">
                                Total de encuestas: {{ totalSurveys }}
                            </span>
                        </div>

                        <!-- Controles de filtrado -->
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                            <!-- Selector de mes -->
                            <div class="relative">
                                <select id="month" v-model="selectMonth" @change="onChangeDate"
                                    class="w-full px-4 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent sm:w-32"
                                    aria-label="Seleccionar mes">
                                    <option :value="null">Mes</option>
                                    <option v-for="(month, index) in months" :key="index" :value="index">
                                        {{ month }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            <!-- Selector de año -->
                            <div class="relative">
                                <select id="year" v-model="selectYear" @change="onChangeDate"
                                    class="w-full px-4 py-2 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent sm:w-32"
                                    aria-label="Seleccionar año">
                                    <option :value="null">Año</option>
                                    <option v-for="(year, index) in years" :key="index" :value="year">
                                        {{ year }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <section>
                        <div class="mx-auto max-w-screen-xl">
                            <!-- Start coding here -->
                            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                                <div>
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead
                                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr class="text-center">
                                                <th scope="col" class="px-4 py-3">Folio</th>
                                                <th scope="col" class="px-4 py-3">Solicitante</th>
                                                <th scope="col" class="px-4 py-3">Fecha de registro</th>
                                                <th scope="col" class="px-4 py-3">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template v-if="surveys.data.length > 0">
                                                <tr class="text-center border-b dark:border-gray-700"
                                                    v-for="survey in surveys.data" :key="survey.id">
                                                    <th scope="row"
                                                        class="px-4 py-3 font-medium text-gray-900 whitespace-wrap dark:text-white">
                                                        {{ survey.misplacement.document_number }}
                                                    </th>
                                                    <td class="text-center px-4 py-3 whitespace-nowrap">
                                                        <div>
                                                            <h4 class="text-gray-700 dark:text-gray-200">
                                                                {{ survey.misplacement.people.name }}
                                                            </h4>
                                                            <p class="text-gray-500 dark:text-gray-400">
                                                                {{ survey.misplacement.people.email }}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span>
                                                            {{ survey.register_date }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 items-center justify-center">
                                                        <Link :href="route('surveys.show', survey.id)"
                                                            class="inline-flex items-center px-4 py-2 bg-blue-500 transition ease-in-out hover:bg-blue-700 text-white text-sm font-medium rounded-md hover:-translate-y-1 hover:scale-105">
                                                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 -960 960 960" fill="currentColor"
                                                            stroke="currentColor">
                                                            <path
                                                                d="m590-160 80 80H240q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h360l200 240v480q0 20-8.5 36.5T768-96L560-302q-17 11-37 16.5t-43 5.5q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 23-5.5 43T618-360l102 104v-356L562-800H240v640h350ZM480-360q33 0 56.5-23.5T560-440q0-33-23.5-56.5T480-520q-33 0-56.5 23.5T400-440q0 33 23.5 56.5T480-360Zm0-80Zm0 0Z" />
                                                        </svg>
                                                        Ver
                                                        </Link>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template v-else>
                                                <tr>
                                                    <td colspan="4"
                                                        class="px-6 py-4 text-center font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        No hay encuentas que mostrar.
                                                    </td>
                                                </tr>
                                            </template>

                                        </tbody>
                                        <tfoot>
                                            <Pagination :colspan="4" :ObjectData="surveys"></Pagination>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
