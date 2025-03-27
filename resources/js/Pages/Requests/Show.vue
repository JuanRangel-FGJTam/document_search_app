<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { defineProps } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { onMounted } from 'vue';
const props = defineProps({
    person: Object,
    misplacement: Object,
    documents: Object,
    placeEvent: Object,
    identification: Object
});

const toast = useToast();

function reSendDocument() {
    router.visit(route('misplacement.reSendDocument', props.misplacement.id), {
        onSuccess: () => {
            toast.info('La constancia se reenviado al correo '+props.person.email+ ' correctamente!');
        }
    });
}
function getTypeClass(typeId) {
    const classMap = {
        '1': 'bg-yellow-100 text-yellow-700',
        '2': 'bg-blue-100 text-blue-700',
        '3': 'bg-emerald-100 text-emerald-700',
        '4': 'bg-red-100 text-red-700'
    };
    return `flex text-center justify-center px-3 py-1 text-sm font-medium rounded-full ${classMap[typeId] || 'bg-gray-200 text-gray-700'}`;
}

onMounted(() => useToast());
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
                    <div class="grid grid-cols-4 gap-4 border p-4 rounded-lg" v-if="person != null">
                        <div v-for="(value, key) in {
                            'Manifestante': person.fullName ?? 'No proporcionado',
                            'CURP': person.curp ?? 'No proporcionado',
                            'RFC': person.rfc ?? 'No proporcionado',
                            'Género': person.genderName ?? 'No proporcionado',
                            'Fecha de Nacimiento': person.birthdateFormated ?? 'No proporcionado',
                            'Edad': `${person.age} años`,
                            'Correo Electrónico': person.email ?? 'No proporcionado'
                        }" :key="key">
                            <p class="font-semibold">{{ key }}</p>
                            <p>{{ value }}</p>
                        </div>
                    </div>
                    <div v-else>
                        <p class="text-red-600 font-semibold">ESTA PERSONA NO EXISTE EN FISCALIA DIGITAL</p>
                        <p class="text-gray-600">Por favor, verifique los datos ingresados o contacte al soporte técnico para más información.</p>
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
                        <template v-if="misplacement.lost_status_id == 3">
                            <div class="flex items-center">
                                <p class="text-center font-semibold text-green-600">Esta solicitud ha sido atendida</p>
                            </div>
                        </template>
                        <button @click="reSendDocument()"
                            v-if="misplacement.lost_status_id == 3 && person"
                            class="text-center px-4 py-2 text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors duration-200 shadow-md">
                        Reenviar Constancia
                        </button>
                        <Link v-if="misplacement.lost_status_id != 4 && person"
                            :href="route('misplacement.cancel', misplacement.id)"
                            class="text-center px-4 py-2 text-red-700 bg-red-100 rounded-lg hover:bg-red-200 transition-colors duration-200 shadow-md">
                        Cancelar Constancia
                        </Link>
                        <p v-if="misplacement.lost_status_id== 4" class="font-semibold text-red-600">
                            Esta solicitud ha sido cancelada
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-4 border p-4 rounded-lg mt-4"
                        v-if="misplacement.lost_status_id == 4">
                        <div>
                            <p class="font-semibold">Fecha de cancelación</p>
                            <p>{{ misplacement.cancellation_date }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Motivo de cancelación</p>
                            <span :class="getTypeClass(misplacement.cancellation_reason_id)">
                                {{ misplacement.cancellation_reason.name }}
                            </span>
                        </div>
                        <div>
                            <p class="font-semibold">Descripción de cancelación</p>
                            <p class="justify">{{ misplacement.cancellation_reason_description ?? 'Sin descripción' }}
                            </p>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4">Datos de la identificación</h3>
                    <div class="grid grid-cols-3 gap-4 border p-4 rounded-lg">
                        <div>
                            <p class="font-semibold">Tipo de Identificación</p>
                            <p>{{ misplacement.misplacement_identifications.identification_type.name }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Folio de Identificación</p>
                            <p>{{ identification.folio ?? 'No disponible' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Fecha de vencimiento</p>
                            <p>{{ identification.valid ?? 'No disponible' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Identificación</p>
                            <img :src="identification.fileUrl" alt="Identificacion"
                                class="h-32 object-cover rounded-lg">
                        </div>
                        <div v-if="identification.fileUrlBack">
                            <p class="font-semibold">Identificación (reversa)</p>
                            <img :src="identification.fileUrlBack" alt="Identificacion"
                                class="h-32 object-cover rounded-lg">
                        </div>
                    </div>
                    <!-- DATOS DEL LUGAR DE LOS HECHOS -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-4">Lugar de los Hechos</h3>
                    <div class="grid grid-cols-4 gap-4 border p-4 rounded-lg">
                        <div v-for="(value, key) in {
                            'Municipio': placeEvent?.municipality?.name ?? 'No proporcionada',
                            'Colonia': placeEvent?.colony?.name ?? 'No proporcionada',
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
                            <p>{{ doc.document_number ?? 'No proporcionado' }}</p>
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
