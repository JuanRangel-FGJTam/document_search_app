<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BackButton from '@/Components/BackButton.vue';
import { defineProps } from 'vue';

const props = defineProps({
    person: Object,
    misplacement: Object,
    personAddress: Object,
    personPhoneHome: Object,
    personPhoneMobile: Object
});


function getTypeClass(typeId) {
    const classMap = {
        '1': 'flex items-center justify-center px-3 py-1 text-sm font-normal rounded-full text-yellow-500 gap-x-2 bg-yellow-100/60 dark:bg-gray-800',
        '2': 'flex items-center justify-center px-3 py-1 text-sm font-normal rounded-full text-blue-500 gap-x-2 bg-blue-100/60 dark:bg-gray-800',
        '3': 'flex items-center justify-center px-3 py-1 text-sm font-normal rounded-full text-emerald-500 gap-x-2 bg-emerald-100/60 dark:bg-gray-800',
        '4': 'flex items-center justify-center px-3 py-1 text-sm font-normal rounded-full text-red-500 gap-x-2 bg-red-100/60 dark:bg-gray-800',
    };
    return classMap[typeId] || 'bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300';
}
</script>

<template>
    <AppLayout title="Solicitud de constancia">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-2">
                    <div class="p-4 sm:flex sm:items-center sm:justify-between">
                        <div class="flex items-center gap-x-3 mb-3 sm:mb-0">
                            <BackButton class="mr-2" :href="route('misplacement.index')" />
                            <h2 class="uppercase font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                                Solicitu de Constancia - {{ misplacement.document_number }}
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="py-2">
                    <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
                        <div class="grid grid-cols-4 gap-4 p-4 mb-6 border shadow-lg rounded">
                            <div>
                                <p class="font-semibold">Manifestante</p>
                                <p>
                                    {{ person.fullName }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">CURP</p>
                                <p>
                                    {{ person.curp ?? 'No proporcionado' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">RFC</p>
                                <p>
                                    {{ person.rfc ?? 'No proporcionado' }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Género</p>
                                <p>
                                    {{ person.genderName }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Fecha de nacimiento</p>
                                <p>
                                    {{ person.birthdateFormated }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Edad</p>
                                <p>
                                    {{ person.age }} años
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Correo Electrónico</p>
                                <p>
                                    {{ person.email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 p-4 mb-4 border shadow-lg rounded">
                            <div>
                                <p class="font-semibold">Folio</p>
                                <p>
                                    {{ misplacement.document_number }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Estatus</p>
                                <p>
                                    <span :class="getTypeClass(misplacement.lost_status_id)" class="w-1/2">
                                        {{ misplacement.lost_status.name }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Fecha de registro</p>
                                <p>
                                    {{ misplacement.registration_date }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Código de solicitud</p>
                                <p>
                                    {{ misplacement.code }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Tipo de Identificación</p>
                                <p>
                                    {{ misplacement.misplacement_identifications.identification_type.name }}
                                </p>
                            </div>

                            <div>
                                <a target="_blank"
                                    class="inline-flex items-center mr-4 px-5 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-blue-800">
                                    Ver documento
                                </a>
                                <button
                                    class="inline-flex items-center px-5 py-2 text-sm font-medium text-center text-red-700 bg-red-100 rounded-lg focus:ring-4 focus:ring-red-200 dark:focus:ring-red-900 hover:bg-red-300">
                                    Cancelar Solicitud
                                </button>
                            </div>

                            <div>

                            </div>
                        </div>


                        <div class="grid grid-cols-4 gap-4 p-4 mb-4 border shadow-lg rounded">
                            <div>
                                <p class="font-semibold">Municipio</p>
                                <p>

                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Colonia</p>
                                <p>

                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Calle</p>
                                <p>
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Fecha de los Hechos</p>
                                <p>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 p-4 mb-4 border shadow-lg rounded">
                            <div>
                                <p class="font-semibold">Documento</p>
                                <p>

                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Folio</p>
                                <p>

                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Titular</p>
                                <p>
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-4 border shadow-lg rounded">
                            <div>
                                <p class="font-semibold">Narración de los hechos</p>
                                <p class="justify">

                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
