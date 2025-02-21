<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { defineProps } from 'vue';

const props = defineProps({
    person: Object,
    misplacement: Object,
    personAddress: Object,
    personPhoneHome: Object,
    personPhoneMobile: Object,
    documents: Object,
    placeEvent: Object
});

function getTypeClass(typeId) {
    const classMap = {
        '1': 'bg-yellow-100 text-yellow-700',
        '2': 'bg-blue-100 text-blue-700',
        '3': 'bg-emerald-100 text-emerald-700',
        '4': 'bg-red-100 text-red-700'
    };
    return `px-3 py-1 text-sm font-medium rounded-full ${classMap[typeId] || 'bg-gray-200 text-gray-700'}`;
}
</script>

<template>
    <AppLayout title="Solicitud de Constancia">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <BackButton class="mr-2" :href="route('misplacement.index')" />
                        <h2 class="text-2xl font-bold text-gray-800">
                            Solicitud de Constancia - {{ misplacement.document_number }}
                        </h2>
                    </div>
                </div>
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <!-- DATOS DEL MANIFESTANTE -->
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos del Manifestante</h3>
                    <div class="grid grid-cols-4 gap-4 border p-4 rounded-lg">
                        <div v-for="(value, key) in {
                            'Manifestante': person.fullName,
                            'CURP': person.curp ?? 'No proporcionado',
                            'RFC': person.rfc ?? 'No proporcionado',
                            'Género': person.genderName,
                            'Fecha de Nacimiento': person.birthdateFormated,
                            'Edad': `${person.age} años`,
                            'Correo Electrónico': person.email
                        }" :key="key">
                            <p class="font-semibold">{{ key }}</p>
                            <p>{{ value }}</p>
                        </div>
                    </div>

                    <!-- DATOS DE LA SOLICITUD -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4">Datos de la Solicitud</h3>
                    <div class="grid grid-cols-3 gap-4 border p-4 rounded-lg">
                        <div>
                            <p class="font-semibold">Folio</p>
                            <p>{{ misplacement.document_number }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Estatus</p>
                            <span :class="getTypeClass(misplacement.lost_status_id)">
                                {{ misplacement.lost_status.name }}
                            </span>
                        </div>
                        <div>
                            <p class="font-semibold">Fecha de Registro</p>
                            <p>{{ misplacement.registration_date }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Código de Solicitud</p>
                            <p>{{ misplacement.code }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Tipo de Identificación</p>
                            <p>{{ misplacement.misplacement_identifications.identification_type.name }}</p>
                        </div>
                        <div class="flex gap-4 mt-4">
                            <template v-if="misplacement.lost_status_id != 3">
                                <button class="px-5 py-2 text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-300"
                                    v-if="misplacement.lost_status_id == 1">Cambiar a revisión</button>
                                <button class="px-5 py-2 text-green-700 bg-green-100 rounded-lg hover:bg-green-300"
                                    v-if="misplacement.lost_status_id == 2">Validar Solicitud</button>
                                <button class="px-5 py-2 text-red-700 bg-red-100 rounded-lg hover:bg-red-300">Cancelar
                                    Solicitud</button>
                            </template>
                            <template v-else>
                                <p class="font-semibold">Esta solicitud ha sido atendida</p>
                            </template>
                        </div>
                    </div>

                    <!-- DATOS DEL LUGAR DE LOS HECHOS -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4">Lugar de los Hechos</h3>
                    <div class="grid grid-cols-4 gap-4 border p-4 rounded-lg">
                        <div v-for="(value, key) in {
                            'Municipio': placeEvent.municipality.name,
                            'Colonia': placeEvent.colony.name,
                            'Calle': placeEvent.street,
                            'Fecha de los Hechos': placeEvent.lost_date
                        }" :key="key">
                            <p class="font-semibold">{{ key }}</p>
                            <p>{{ value }}</p>
                        </div>
                    </div>

                    <!-- DOCUMENTOS EXTRAVIADOS -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4">Documentos Extraviados</h3>
                    <div v-for="doc in documents" :key="doc.id" class="grid grid-cols-3 gap-4 border p-4 rounded-lg">
                        <div>
                            <p class="font-semibold">Documento</p>
                            <p>{{ doc.document_type.name }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Folio</p>
                            <p>{{ doc.document_number }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Titular</p>
                            <p>{{ doc.document_owner }}</p>
                        </div>
                    </div>

                    <!-- NARRACIÓN DE LOS HECHOS -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4">Narración de los Hechos</h3>
                    <div class="border p-4 rounded-lg">
                        <p class="text-gray-800">{{ placeEvent.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
