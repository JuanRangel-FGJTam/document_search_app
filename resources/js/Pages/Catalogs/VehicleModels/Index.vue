<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';
import { useToast } from 'vue-toastification';
const props = defineProps({
    models: {
        type: Object,
    },
});

const toast = useToast();
const confirmDelete = ref(false);
const selectedModelId = ref(null);

function deleteModel(id) {
    selectedModelId.value = id;
    confirmDelete.value = true;
}

function confirmDeleteBrand() {
    router.delete(route('vehicleModel.delete', selectedModelId.value), {
        onSuccess: () => {
            toast.success('Marca eliminada correctamente');
            confirmDelete.value = false;
        },
        onError: (errors) => {
            toast.error(errors.message || 'No se pudo eliminar la marca.');
            confirmDelete.value = false;
        }
    });
}
</script>

<template>
    <AppLayout title="Marcas de vehículos">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- component -->
                    <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Encabezado y contador -->
                        <div class="flex items-center gap-x-3">
                            <h2 class="text-lg font-semibold leading-tight text-gray-800 uppercase dark:text-gray-200">
                                Modelos de vehículos
                            </h2>
                        </div>
                        <div>
                            <Link :href="route('vehicleModel.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 transition ease-in-out hover:bg-blue-700 text-white text-sm font-medium rounded-md hover:-translate-y-1 hover:scale-105">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z" />
                            </svg>
                            Agregar modelo
                            </Link>
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
                                                <th scope="col" class="px-4 py-3">Nombre</th>
                                                <th scope="col" class="px-4 py-3">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template v-if="models.data.length > 0">
                                                <tr class="text-center border-b dark:border-gray-700"
                                                    v-for="model in models.data" :key="model.id">
                                                    <th scope="row"
                                                        class="px-4 py-3 font-medium text-gray-900 whitespace-wrap dark:text-white">
                                                        {{ model.name }}
                                                    </th>
                                                    <td class="px-4 py-3 items-center justify-center">
                                                        <div class="flex justify-center gap-2">
                                                            <Link :href="route('vehicleModel.edit', model.id)"
                                                                class="inline-flex items-center px-4 py-2 bg-yellow-500 transition ease-in-out hover:bg-yellow-700 text-white text-sm font-medium rounded-md hover:-translate-y-1 hover:scale-105">
                                                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 24 24" fill="currentColor">
                                                                <path
                                                                    d="M14.06 2.94a1.5 1.5 0 0 1 2.12 0l4.88 4.88a1.5 1.5 0 0 1 0 2.12l-10 10a1.5 1.5 0 0 1-1.06.44H5.5a1.5 1.5 0 0 1-1.5-1.5v-4.5c0-.4.16-.78.44-1.06l10-10ZM15 5.12 18.88 9 17 10.88 13.12 7 15 5.12ZM12 8.12 4 16.12V19h2.88l8-8L12 8.12Z" />
                                                            </svg>
                                                            Editar
                                                            </Link>
                                                            <button @click="deleteModel(model.id)"
                                                                class="inline-flex items-center px-4 py-2 bg-red-500 transition ease-in-out hover:bg-red-700 text-white text-sm font-medium rounded-md hover:-translate-y-1 hover:scale-105">
                                                                <svg class="w-5 h-5 mr-2"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 24 24" fill="currentColor">
                                                                    <path
                                                                        d="M9 3V4H4V6H5V19C5 20.1 5.9 21 7 21H17C18.1 21 19 20.1 19 19V6H20V4H15V3H9ZM7 6H17V19H7V6ZM9 8V17H11V8H9ZM13 8V17H15V8H13Z" />
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
                                                        class="px-6 py-4 text-center font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        No hay modelos de vehículos que mostrar.
                                                    </td>
                                                </tr>
                                            </template>

                                        </tbody>
                                        <tfoot>
                                            <Pagination :colspan="2" :ObjectData="models"></Pagination>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                    <div v-if="confirmDelete"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">¿Estás seguro que deseas eliminar esta
                                marca?</h3>
                            <p class="text-sm text-gray-600 mb-6">Esta acción no se puede deshacer.</p>
                            <div class="flex justify-end gap-2">
                                <button @click="confirmDelete = false"
                                    class="px-4 py-2 rounded-md bg-gray-300 hover:bg-gray-400 text-sm">Cancelar</button>
                                <button @click="confirmDeleteBrand"
                                    class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
