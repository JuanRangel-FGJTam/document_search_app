<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchInput from '@/Components/SearchInputLarge.vue';

const props = defineProps({
    search: {
        type: String,
        default: ""
    }
});

</script>

<template>
    <AppLayout title="Dashboard">
        <div class="p-6 flex items-center justify-center h-[45vh]">
            <div class="flex flex-col max-w-screen-lg mx-auto w-full">
                <div class="w-full text-center">
                    <h1 class="my-6 text-3xl uppercase font-semibold text-gray-700 dark:text-gray-200">Busqueda de placas</h1>
                </div>
                <SearchInput :initial-search="props.search" />
            </div>
        </div>

        <div class="p-6 flex flex-col max-w-screen-xl mx-auto border-t">
            <h1 class="my-2 text-xl uppercase font-semibold text-gray-700 dark:text-gray-200">Ultimos reportes</h1>
            <div class="my-2 grid grid-cols-4 gap-4">
                <div class="bg-white border shadow h-[6rem]"> </div>
                <div class="bg-white border shadow h-[6rem]"> </div>
                <div class="bg-white border shadow h-[6rem]"> </div>
                <div class="bg-white border shadow h-[6rem]"> </div>
                <div class="bg-white border shadow h-[6rem]"> </div>
                <div class="bg-white border shadow h-[6rem]"> </div>
                <div class="bg-white border shadow h-[6rem]"> </div>
            </div>
        </div>
    </AppLayout>
</template>
