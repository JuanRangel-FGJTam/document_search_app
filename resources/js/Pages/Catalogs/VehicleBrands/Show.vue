<script setup>
import { onMounted, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import ModalConfirmation from '@/Components/ModalConfirmation.vue';
import Modal from '@/Components/Modal.vue';
import BackButton from '@/Components/BackButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
const props = defineProps({
    errors: Object,
    brand: Object,
});
const toast = useToast();
const modelId = ref(null);
let route_delete = null;
let showModal = ref(false);
const modalMaxWidth = '2xl';
const modalCloseable = true;
const openModal = (model_id) => {
    if (model_id) {
        modelId.value = model_id;
        route_delete = route('vehicleSubBrand.delete', { 'vehicleBrand_id': modelId.value, 'view': 'vehicleBrand.show' });
    } else {
        route_delete = route('vehicleBrand.delete', props.brand.id);
    }
    showModal.value = true;
};
const closeModal = () => {
    showModal.value = false;
};

const confirmModal = () => {
    router.delete(route_delete, {
        preserveScroll: true,
        onSuccess() {
            toast.success('Datos eliminados correctamente!');
        },
        onError(errors) {
            if (errors.error) {
                toast.error(errors.error);
            }
        },
    });
    closeModal();
};

const form = useForm({
    name: null,
});

// Modal attributes and functions
const showModalModels = ref(false);
const modalMaxWidthModels = '2xl';
const modalCloseableModels = true;
const modalHeader = 'Agregar SubMarca';

const openModalModels = () => {
    showModalModels.value = true;
};
const closeModalModels = () => {
    form.reset();
    showModalModels.value = false;
};

const saveModal = () => {
    form.post(route('vehicleBrand.storeSubBrand', props.brand.id), {
        onSuccess: () => {
            form.reset();
            showModalModels.value = false;
            toast.success('SubMarca agregado correctamente');
        },
        onError: (errors) => {
            console.log(errors);
            if (errors.error) {
                toast.error(errors.error);
            }
        }
    });
};


</script>

<template>
    <AppLayout title="Detalles de marca">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-4 sm:flex sm:items-center sm:justify-between">
                        <div class="flex items-center gap-x-3 mb-3 sm:mb-0">
                            <BackButton :href="route('vehicleBrand.index')" class="mr-2"></BackButton>
                            <h2 class="uppercase font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight">
                                Marca | {{ brand.name }}
                            </h2>
                        </div>
                    </div>
                    <div
                        class="relative flex flex-col w-full min-w-0 break-words bg-clip-border rounded-2xl border-stone-200 bg-light/30 draggable">
                        <div class="px-9 pt-4 flex-auto min-h-[70px] pb-0 bg-transparent">
                            <div class="flex flex-wrap mb-6 xl:flex-nowrap">
                                <div class="grow">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <div class="flex flex-col">
                                            <div class="flex items-center">
                                                <div
                                                    class="uppercase text-secondary-inverse hover:text-primary transition-colors duration-200 ease-in-out font-semibold text-lg mr-1">
                                                    {{ brand.name }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap my-auto gap-2">
                                            <button @click="openModalModels()"
                                                class="inline-flex items-center px-2 py-2 bg-green-500 border border-transparent rounded-md text-base text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-500 hover:-translate-y-2">
                                                <svg class="w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" height="24px"
                                                    viewBox="0 -960 960 960" width="24px" fill="currentColor">
                                                    <path
                                                        d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                                                </svg>
                                                Agregar SubMarca
                                            </button>
                                            <Link :href="route('vehicleBrand.edit', brand.id)"
                                                class="inline-flex items-center px-2 py-2 bg-blue-500 border border-transparent rounded-md text-base text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-500 hover:-translate-y-2">
                                            <svg class="w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" height="24px"
                                                viewBox="0 -960 960 960" width="24px" fill="currentColor">
                                                <path
                                                    d="M160-400v-80h280v80H160Zm0-160v-80h440v80H160Zm0-160v-80h440v80H160Zm360 560v-123l221-220q9-9 20-13t22-4q12 0 23 4.5t20 13.5l37 37q8 9 12.5 20t4.5 22q0 11-4 22.5T863-380L643-160H520Zm300-263-37-37 37 37ZM580-220h38l121-122-18-19-19-18-122 121v38Zm141-141-19-18 37 37-18-19Z" />
                                            </svg>
                                            Editar
                                            </Link>
                                            <button @click="openModal()"
                                                class="inline-flex items-center px-2 py-2 bg-red-500 border border-transparent rounded-md text-base text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-500 hover:-translate-y-2">
                                                <svg class="w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg"
                                                    height="24px" viewBox="0 -960 960 960" width="24px"
                                                    fill="currentColor">
                                                    <path
                                                        d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z" />
                                                </svg>
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-full h-px border-neutral-200">
                    <div class="grid grid-cols-1">
                        <div class="bg-white rounded-lg p-4">
                            <div class="w-full flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                                    SubMarca
                                </h3>
                            </div>
                            <section>
                                <div class="mx-auto max-w-screen-xl">
                                    <div
                                        class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                                <thead
                                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                    <tr class="text-center">
                                                        <th scope="col" class="px-4 py-3">Nombre</th>
                                                        <th scope="col" class="px-4 py-3">
                                                            Acciones
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template v-if="brand.sub_brands.length > 0">
                                                        <tr class="text-center border-b dark:border-gray-700"
                                                            v-for="model in brand.sub_brands"
                                                            :key="model.id">
                                                            <td class="text-start px-4 py-3 whitespace-nowrap">
                                                                <div class="text-center">
                                                                    <h4
                                                                        class="font-bold text-gray-700 dark:text-gray-200">
                                                                        {{ model.name }}
                                                                    </h4>
                                                                </div>
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                <div class="flex justify-center items-center gap-2">
                                                                    <Link
                                                                        :href="route('vehicleSubBrand.edit', model.id)"
                                                                        class="bg-blue-500 text-white flex items-center py-2 px-4 rounded-md hover:bg-blue-600 dark:hover:bg-blue-400 dark:hover:text-white transition">
                                                                    <svg class="w-5 h-5 mr-2"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 -960 960 960" fill="currentColor">
                                                                        <path
                                                                            d="M160-400v-80h280v80H160Zm0-160v-80h440v80H160Zm0-160v-80h440v80H160Zm360 560v-123l221-220q9-9 20-13t22-4q12 0 23 4.5t20 13.5l37 37q8 9 12.5 20t4.5 22q0 11-4 22.5T863-380L643-160H520Zm300-263-37-37 37 37ZM580-220h38l121-122-18-19-19-18-122 121v38Zm141-141-19-18 37 37-18-19Z" />
                                                                    </svg>
                                                                    Editar
                                                                    </Link>
                                                                    <button @click="openModal(model.id)"
                                                                        class="bg-red-500 text-white flex items-center py-2 px-4 rounded-md hover:bg-red-600 dark:hover:bg-red-400 dark:hover:text-white transition">
                                                                        <svg class="w-5 h-5 mr-2"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            height="24px" viewBox="0 -960 960 960"
                                                                            width="24px" fill="currentColor">
                                                                            <path
                                                                                d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
                                                                        </svg>
                                                                        Eliminar
                                                                    </button>
                                                                </div>
                                                            </td>

                                                        </tr>
                                                    </template>
                                                    <template v-else>
                                                        <tr>
                                                            <td colspan="2"
                                                                class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                                                No hay submarcas registradas.
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ModalConfirmation :show="showModal" :maxWidth="modalMaxWidth" :closeable="modalCloseable" @close="closeModal"
            @save="confirmModal">
            <template #title>
                ¿Estás seguro de eliminar este registro?
            </template>
            <template #content>
                La información se eliminará y no podrá ser al recuperada.
            </template>
        </ModalConfirmation>

        <Modal :show="showModalModels" :maxWidth="modalMaxWidthModels" :closeable="modalCloseableModels"
            :header="modalHeader" @close="closeModalModels" @save="saveModal">
            <form @submit.prevent="saveModal">
                <div class="flex flex-wrap -mx-3">
                    <div class="flex-grow w-2/4 pr-2 py-2">
                        <InputLabel for="name" value="Nombre de submarca" />
                        <input type="text" name="name" id="name" v-model="form.name"
                            class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Ingrese nombre de la submarca">
                        <InputError v-if="errors.name" :message="errors.name" />
                    </div>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>
