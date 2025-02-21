<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
});


</script>
<template>
    <Link :href="href"
        class="text-white bg-gray-800 hover:bg-gray-700 focus:ring-4 focus:outline-none font-medium rounded-lg text-sm p-2.5 text-center inline-flex items-center me-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
        <path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z" />
    </svg>
    </Link>
</template>
