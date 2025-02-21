<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryLink from '@/Components/PrimaryLink.vue';
</script>

<template>
    <AppLayout title="Solicitudes Constancias">
        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <!-- component -->
                    <div class="p-4 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-x-3">
                                <h2
                                    class="uppercase font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight">
                                    Solicitudes de Constancias
                                </h2>
                                <span
                                    class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full dark:bg-gray-800 dark:text-blue-400">
                                    Total de solicitudes
                                </span>
                            </div>
                        </div>
                    </div>
                    <section>
                        <div class="mx-auto max-w-screen-xl">
                            <!-- Start coding here -->
                            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                                <div
                                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                                    <div class="w-full md:w-1/2">
                                        <div class="flex items-center">
                                            <label for="simple-search" class="sr-only">Search</label>
                                            <div class="relative w-full">
                                                <div
                                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                    <svg aria-hidden="true"
                                                        class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                                        fill="currentColor" viewbox="0 0 20 20"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd"
                                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <input type="text" id="simple-search"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                                    placeholder="Busca por folio, solicitante o empleado asignado..."
                                                    required="">
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                                        <div class="flex items-center space-x-3 w-full md:w-auto">
                                            <div>
                                                <div class="relative inline-block w-42">
                                                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                                                        viewbox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <select
                                                        class="appearance-none w-full bg-white border border-gray-300 text-gray-900 py-2 pl-10 pr-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                                                        <option>Todos</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="relative inline-block w-64">
                                                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                                                        viewbox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <select
                                                        class="appearance-none w-full bg-white border border-gray-300 text-gray-900 py-2 pl-10 pr-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                                                        <option :value="null">Departamentos</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead
                                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr class="text-center">
                                                <th scope="col" class="px-4 py-3">Folio</th>
                                                <th scope="col" class="px-4 py-3">Solicitante</th>
                                                <th scope="col" class="px-4 py-3">Asignado</th>
                                                <th scope="col" class="px-4 py-3">Creado</th>
                                                <th scope="col" class="px-4 py-3">Departamento</th>
                                                <th scope="col" class="px-4 py-3">Status</th>
                                                <th scope="col" class="px-4 py-3">
                                                    Acciones
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>


                                        </tbody>
                                        <tfoot>
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
