<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';
import BackButton from '@/Components/BackButton.vue';
const props = defineProps({
    brands: {
        type: Object,
    },
});
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
                            <BackButton class="mr-2" :href="route('catalogs.index')" />
                            <h2 class="text-lg font-semibold leading-tight text-gray-800 uppercase dark:text-gray-200">
                                Marcas de vehículos
                            </h2>
                        </div>
                        <!-- Botón para agregar nueva marca -->
                        <div>
                            <Link :href="route('vehicleBrand.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 transition ease-in-out hover:bg-blue-700 text-white text-sm font-medium rounded-md hover:-translate-y-1 hover:scale-105">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z" />
                            </svg>
                            Agregar marca
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
                                            <template v-if="brands.data.length > 0">
                                                <tr class="text-center border-b dark:border-gray-700"
                                                    v-for="brand in brands.data" :key="brand.id">
                                                    <th scope="row"
                                                        class="px-4 py-3 font-medium text-gray-900 whitespace-wrap dark:text-white">
                                                        {{ brand.name }}
                                                    </th>
                                                    <td class="px-4 py-3 items-center justify-center">
                                                        <Link :href="route('vehicleBrand.show', brand.id)"
                                                            class="inline-flex items-center px-2 py-2 bg-gray-800 border border-transparent rounded-md text-base text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-500 hover:-translate-y-2">
                                                        <svg class="w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg"
                                                            height="24px" viewBox="0 -960 960 960" width="24px"
                                                            fill="currentColor">
                                                            <path
                                                                d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
                                                        </svg>
                                                        Ver detalles
                                                        </Link>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template v-else>
                                                <tr>
                                                    <td colspan="2"
                                                        class="px-6 py-4 text-center font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                        No hay marcas de vehículos que mostrar.
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot>
                                            <Pagination :colspan="2" :ObjectData="brands"></Pagination>
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
