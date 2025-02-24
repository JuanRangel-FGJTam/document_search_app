<script setup>
import { onMounted } from 'vue';
import { ref } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import ModalConfirmation from '@/Components/ModalConfirmation.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
const props = defineProps({
    errors: Object,
    cancellationReasons: Object,
    misplacement: Object,
    today: String
})
// Create a form using Inertia's useForm
const form = useForm({
    cancellation_reason: '',
    message: null,
    deadline: props.today
});

const toast = useToast();
//MODAL ATTRIBUTES AND FUNCTIONS
let showModal = ref(false);
const modalMaxWidth = '2xl';
const modalCloseable = true;

const openModal = () => {
    showModal.value = true;
};
const closeModal = () => {
    showModal.value = false;
};
const confirmModal = () => {
    form.post(route('misplacement.store.cancel',props.misplacement.id), {
        onError: (errors) => {
            closeModal();
        },
        onSuccess: () => {
            toast.success('Solicitud cancelada correctamente');
        }
    });
    closeModal();
};

</script>

<template>
    <AppLayout title="Cancelación de solicitud">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="w-full px-2 py-2">
                        <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Formulario de cancelación de constancia</h2>
                        <form @submit.prevent="submit">
                            <div class="grid gap-4 mb-4 sm:grid-cols-2 sm:gap-6 sm:mb-5">
                                <div class="col-span-1">
                                    <label for="deadline"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Fecha de cancelación
                                    </label>
                                    <input v-model="form.deadline" id="deadline" type="date" name="deadline"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="Select date">
                                    <InputError v-if="errors.deadline" :message="errors.deadline" />
                                </div>
                                <div class="col-span-1">
                                    <label for="deadline"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Motivo de cancelación
                                    </label>
                                    <select id="cancellation_reason" name="cancellation_reason"
                                        v-model="form.cancellation_reason"
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        <option disabled value="">Seleccione un elemento</option>
                                        <option v-for="reason in cancellationReasons" :key="reason.id"
                                            v-bind:value="reason.id">
                                            {{ reason.name }}
                                        </option>
                                    </select>
                                    <InputError v-if="errors.cancellation_reason" :message="errors.cancellation_reason" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="message"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Descripción de motivo de cancelación
                                    </label>
                                    <textarea v-model="form.message" id="message" rows="7"
                                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        placeholder="Describa el motivo del por que desea cancelar esta solicitud..."></textarea>
                                    <InputError v-if="errors.message" :message="errors.message" />
                                </div>
                            </div>
                            <div class="flex items-center justify-between space-x-4">
                                <Link :href="route('misplacement.show',misplacement.id)"
                                    class="text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"
                                    fill="currentColor">
                                    <path
                                        d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                                </svg>
                                Cancelar
                                </Link>
                                <button type="button" @click="openModal"
                                    class="flex items-center px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-150 ease-in-out text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-6 h-6 mr-2"
                                        fill="currentColor">
                                        <path d="M18.9 35.7 7.7 24.5 9.85 22.35 18.9 31.4 38.1 12.2 40.25 14.35Z" />
                                    </svg>
                                    Cancelar reporte
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <ModalConfirmation :show="showModal" :maxWidth="modalMaxWidth" :closeable="modalCloseable" @close="closeModal"
            @save="confirmModal">
            <template #title>
                ¿Estás seguro de cancelar esta solicitud?
            </template>
            <template #content>
                La solicitud será cancelada definitivamente, no podrá ser recuperado.
            </template>
        </ModalConfirmation>
    </AppLayout>
</template>
