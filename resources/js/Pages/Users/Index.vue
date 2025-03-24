<script setup>
import { onMounted, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import ModalConfirmation from '@/Components/ModalConfirmation.vue';
import PrimaryLink from '@/Components/PrimaryLink.vue';
import Pagination from '@/Components/Pagination.vue';
const props = defineProps({
    users: Object,
})
const DELETE_TYPE = 1;
const REFUND_TYPE = 2;

const toast = useToast();
let userId = ref(null);
let action_type = ref(null);
let showModal = ref(false);
const modalTitle = ref('');
const modalContent = ref('');
const modalMaxWidth = '2xl';
const modalCloseable = true;
const openModal = (user_id, action) => {
    userId.value = user_id;
    action_type.value = action;
    if (action === DELETE_TYPE) {
        modalTitle.value = '¿Estás seguro de dar de baja este usuario?';
        modalContent.value = 'El usuario se dará de baja y no podrá ingresar al sistema.';
    } else if (action === REFUND_TYPE) {
        modalTitle.value = '¿Estás seguro de devolver este usuario?';
        modalContent.value = 'El usuario será reactivado y podrá ingresar al sistema nuevamente.';
    }
    showModal.value = true;
};
const closeModal = () => {
    showModal.value = false;
};
//toast.success('Usuario dado de baja correctamente');

const confirmModal = () => {
    let routeName;
    let successMessage;
    if (action_type.value === DELETE_TYPE) {
        routeName = route('users.delete', userId.value);
        successMessage = 'Usuario dado de baja correctamente';
    } else {
        routeName = route('users.refund', userId.value);
        successMessage = 'Usuario reactivado correctamente';
    }
    router.visit(routeName, {
        preserveScroll: true,
        onSuccess() {
            toast.success(successMessage);
        }
    });
    closeModal();
};
function getTypeClass(typeId) {
    const classMap = {
        '1': 'inline px-3 py-1 text-sm font-normal rounded-full text-pink-500 gap-x-2 bg-pink-100/60 dark:bg-gray-800',
        '2': 'inline px-3 py-1 text-sm font-normal rounded-full text-blue-500 gap-x-2 bg-blue-100/60 dark:bg-gray-800',
        '3': 'inline px-3 py-1 text-sm font-normal rounded-full text-indigo-500 gap-x-2 bg-indigo-100/60 dark:bg-gray-800',
        '4': 'inline px-3 py-1 text-sm font-normal rounded-full text-purple-500 gap-x-2 bg-purple-100/60 dark:bg-gray-800',
        '5': 'inline px-3 py-1 text-sm font-normal rounded-full text-emerald-500 gap-x-2 bg-emerald-100/60 dark:bg-gray-800',
        '6': 'inline px-3 py-1 text-sm font-normal rounded-full text-red-500 gap-x-2 bg-red-100/60 dark:bg-gray-800',
    };
    return classMap[typeId] || 'bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300';
}

</script>

<template>
    <AppLayout title="Usuarios">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- component -->
                    <div class="p-4 sm:flex sm:items-center sm:justify-between dark:bg-gray-700  shadow-md">
                        <div>
                            <div class="flex items-center gap-x-3">
                                <h2 class="uppercase font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight">
                                    Usuarios
                                </h2>
                            </div>
                        </div>
                        <div class="flex w-full sm:w-auto gap-x-2 mt-4 sm:mt-0">
                            <PrimaryLink :href="route('users.create')" class="flex items-center justify-center w-full sm:w-auto h-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-300">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                    <path d="M720-400v-120H600v-80h120v-120h80v120h120v80H800v120h-80Zm-360-80q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm80-80h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm0 400Z"/>
                                </svg>
                                <span>Agregar Usuario</span>
                            </PrimaryLink>
                        </div>
                    </div>
                    <section>
                        <div class="mx-auto max-w-screen-xl">
                            <!-- Start coding here -->
                            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead
                                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr class="text-center">
                                                <th scope="col" class="px-4 py-3">Usuario</th>
                                                <th scope="col" class="px-4 py-3">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template v-if="users.data.length > 0">
                                                <tr class="text-center border-b dark:border-gray-700"
                                                    v-for="user in users.data" :key="user.id">
                                                    <td class="text-start px-4 py-3 whitespace-nowrap">
                                                        <div class="text-center">
                                                            <h4 class="text-gray-700 dark:text-gray-200">
                                                                {{ user.name }}
                                                            </h4>
                                                            <p class="text-gray-500 dark:text-gray-400">
                                                                {{ user.email }}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 flex items-center justify-center space-x-2">
                                                        <Link :href="route('users.edit', user.id)"
                                                            class="text-blue-500 flex items-center py-2 px-4 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-600 dark:hover:text-white transition duration-300">
                                                            <svg class="w-5 h-5 mr-2"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 -960 960 960" fill="currentColor">
                                                                <path
                                                                    d="M160-400v-80h280v80H160Zm0-160v-80h440v80H160Zm0-160v-80h440v80H160Zm360 560v-123l221-220q9-9 20-13t22-4q12 0 23 4.5t20 13.5l37 37q8 9 12.5 20t4.5 22q0 11-4 22.5T863-380L643-160H520Zm300-263-37-37 37 37ZM580-220h38l121-122-18-19-19-18-122 121v38Zm141-141-19-18 37 37-18-19Z" />
                                                            </svg>
                                                            Editar
                                                        </Link>
                                                        <button @click="openModal(user.id, DELETE_TYPE)"
                                                            class="text-red-500 flex items-center py-2 px-4 rounded-lg hover:bg-red-100 dark:hover:bg-red-600 dark:hover:text-white transition duration-300">
                                                            <svg class="w-5 h-5 mr-2"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 -960 960 960"
                                                                fill="currentColor">
                                                                <path
                                                                    d="M640-520v-80h240v80H640Zm-280 40q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm80-80h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm0 400Z" />
                                                            </svg>
                                                            Dar baja
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template v-else>
                                                <tr>
                                                    <td colspan="4"
                                                        class="px-6 py-4 text-center font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        No hay usuarios.
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot>
                                            <Pagination :colspan="4" :ObjectData="users"></Pagination>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <ModalConfirmation :show="showModal" :maxWidth="modalMaxWidth" :closeable="modalCloseable"
            @close="closeModal" @save="confirmModal">
            <template #title>
                {{ modalTitle }}
            </template>
            <template #content>
                {{ modalContent }}
            </template>
        </ModalConfirmation>
    </AppLayout>
</template>
