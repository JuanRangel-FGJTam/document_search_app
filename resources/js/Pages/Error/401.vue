<script setup>
import MainLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

</script>

<template>
    <MainLayout>
        <div class="flex flex-col justify-center items-center px-6 mx-auto h-screen xl:px-0 dark:bg-gray-900">
            <div class="block w-1/6">
                <svg fill="#000000" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M7,0A3.93,3.93,0,0,0,6,7.72,6,6,0,0,1,10.77,5a3.8,3.8,0,0,0,.17-1.07A3.93,3.93,0,0,0,7,0ZM5.3,9.12H4.5A4.52,4.52,0,0,0,0,13.62,2.34,2.34,0,0,0,0,14H5.81A6,6,0,0,1,5,11,6.17,6.17,0,0,1,5.3,9.12Z"></path> <path class="secondary" d="M11,6a5,5,0,1,0,5,5A5,5,0,0,0,11,6Zm.59,8.3H10.41V13.13h1.18Zm0-2.45H10.43l-.1-4.15h1.34Z"></path> </g> </g></svg>
            </div>
            <div class="text-center xl:max-w-4xl">
                <h1 class="mb-3 text-2xl font-bold leading-tight text-gray-900 sm:text-4xl lg:text-5xl dark:text-white">
                    No tienes acceso a esta sección del sistema.
                </h1>
                <p class="mb-5 text-base font-normal text-gray-500 md:text-lg dark:text-gray-400">
                    Contacta a nuestros administradores para obtener acceso.
                </p>
                <Link :href="route('dashboard')" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4
                    focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex
                    items-center mr-3 dark:bg-blue-600 dark:hover:bg-primary-700 dark:focus:ring-blue-800">
                <svg class="mr-2 -ml-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                Volver al inicio
                </Link>
            </div>
        </div>
    </MainLayout>
</template>
