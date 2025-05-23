<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CarPlate from '@/Components/CarPlate.vue';

const props = defineProps({
    searchResult: Object,
});

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString();
}
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="p-6 flex flex-col max-w-screen-lg mx-auto border-t">
            <div class="bg-white rounded shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200 ">REPORTE: {{searchResult.documentNumber}}</h2>

                <div class="flex gap-3 items-center">

                    <CarPlate class="w-[300px]" :plateNumber="searchResult.plateNumber" :serialNumber="searchResult.serialNumber" />

                    <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600">Folio:</p>
                            <p class="font-medium">{{ searchResult.documentNumber }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Estado:</p>
                            <p class="font-medium">{{ searchResult.misplacement.statusName }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Fecha de Registro:</p>
                            <p class="font-medium">{{ formatDate(searchResult.registerDate) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Descripcion del Vehiculo:</p>
                            <p class="font-medium">{{ searchResult.carDescription ?? "*No disponible" }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-t mt-2 pt-2">
                    <div class="">
                        <h3 class="text-lg font-semibold mb-2 text-gray-700 dark:text-gray-200">Lugar y Hechos</h3>
                        <div v-if="searchResult.placeEvent" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">Fecha de Extravío:</p>
                                <p class="font-medium">{{ formatDate(searchResult.placeEvent.lostDate) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Código Postal:</p>
                                <p class="font-medium">{{ searchResult.placeEvent.zipCode }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Municipio:</p>
                                <p class="font-medium">{{ searchResult.placeEvent.municipalityName }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Colonia:</p>
                                <p class="font-medium">{{ searchResult.placeEvent.colonyName }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Calle:</p>
                                <p class="font-medium">{{ searchResult.placeEvent.street }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-gray-600">Descripción de los hechos:</p>
                                <p class="font-medium">{{ searchResult.placeEvent.description }}</p>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 italic">*No disponible</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded shadow-md overflow-hidden py-3">
                    <div class="px-4 py-2">
                        <h2 class="text-lg uppercase text-gray-700 dark:text-gray-200 font-semibold">Datos de la Persona</h2>
                    </div>
                    <div class="p-4" v-if="searchResult.person">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Nombre:</p>
                            <p class="font-medium">{{ searchResult.person.fullName }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">CURP:</p>
                            <p class="font-medium">{{ searchResult.person.curp }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Correo:</p>
                            <p class="font-medium">{{ searchResult.person.email }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Fecha de Nacimiento:</p>
                            <p class="font-medium">{{ searchResult.person.birthDate }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Genero:</p>
                            <p class="font-medium">{{ searchResult.person.genderName }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Profesion:</p>
                            <p class="font-medium">{{ searchResult.person.occupationName }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Edad:</p>
                            <p class="font-medium">{{ searchResult.person.age }}</p>
                        </div>
                    </div>
                    <div class="p-4" v-else>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Nombre:</p>
                            <p class="font-medium">{{ searchResult.fullName }}</p>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Card -->
                <div class="bg-white rounded shadow-md overflow-hidden py-3">
                    <div class="px-4 py-2">
                        <h2 class="text-lg uppercase text-gray-700 dark:text-gray-200 font-semibold">Datos del Vehiculo</h2>
                    </div>
                    <div class="p-4" v-if="searchResult.vehicle">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Placa:</p>
                            <p class="font-medium">{{ searchResult.vehicle.plateNumber }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Nuero de Serie:</p>
                            <p class="font-medium">{{ searchResult.vehicle.serieNumber }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Marca:</p>
                            <p class="font-medium">{{ searchResult.vehicle.brandName }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Sub Marca:</p>
                            <p class="font-medium">{{ searchResult.vehicle.subBrandName }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Tipo:</p>
                            <p class="font-medium">{{ searchResult.vehicle.typeName }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Modelo:</p>
                            <p class="font-medium">{{ searchResult.vehicle.modelYear }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Dueño</p>
                            <p class="font-medium">{{ searchResult.vehicle.owner }}</p>
                        </div>
                    </div>

                    <div class="p-4" v-else>
                        <p class="text-gray-700 dark:text-gray-200 text-center uppercase">
                            *No disponible
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
