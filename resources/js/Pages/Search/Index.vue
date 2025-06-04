<script setup>
import { onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchInput from '@/Components/SearchInputSm.vue';
import ResultCardLoading from '@/Components/ResultLoadingCard.vue';
import ResultCard from '@/Components/ResultCard.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';

const props = defineProps({
    search: {
        type: String,
        default: ""
    },
    results: {
        type: Array,
        default: undefined
    },
    searchTypes: Object,
    searchType: String
});

const proccessing = ref(true);

onMounted(()=>{
    proccessing.value = true;

    router.reload({
        only: ['results'],
        onFinish:()=>{
            proccessing.value = false;
        }
    });
});

</script>

<template>
    <AppLayout title="Dashboard">
        <div class="p-6 flex flex-col max-w-screen-xl mx-auto border-t">

            <div class="row">
                <SearchInput :initial-search="props.search" :types="searchTypes" :searchType="searchType"/>
            </div>

            <h1 class="flex items-center border-t pt-4 gap-2 my-2 text-xl uppercase font-semibold text-gray-700 dark:text-gray-200">
                <span>Resultados</span>
                <AnimateSpin v-if="!results" class="w-4 h-4" />
            </h1>
            
            <div v-if="!results" class="my-2 grid grid-cols-4 gap-4">
                <ResultCardLoading />
                <ResultCardLoading />
                <ResultCardLoading />
            </div>

            <div v-else-if="results && results.length >= 1 " class="my-2 flex flex-wrap justify-around gap-4">
                <Link :href="route('search.result', item.vehicleId)" v-for="(item, index) in results" :key="index" >
                    <ResultCard :item="item" />
                </Link>
            </div>

            <div v-else class="my-2 flex flex-col align-middle items-center gap-4">
                <div class="text-gray-500 w-12 h-12 mt-[3rem]">
                    <svg aria-hidden="true" data-prefix="fal" data-icon="book-open" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-book-open fa-w-18 fa-7x"><path fill="currentColor" d="M514.91 32h-.16c-24.08.12-144.75 8.83-219.56 48.09-4.05 2.12-10.33 2.12-14.38 0C205.99 40.83 85.32 32.12 61.25 32h-.16C27.4 32 0 58.47 0 91.01v296.7c0 31.41 25.41 57.28 57.85 58.9 34.77 1.76 122.03 8.26 181.89 30.37 5.27 1.95 10.64 3.02 16.25 3.02h64c5.62 0 10.99-1.08 16.26-3.02 59.87-22.11 147.12-28.61 181.92-30.37 32.41-1.62 57.82-27.48 57.82-58.89V91.01C576 58.47 548.6 32 514.91 32zM272 433c0 8.61-7.14 15.13-15.26 15.13-1.77 0-3.59-.31-5.39-.98-62.45-23.21-148.99-30.33-191.91-32.51-15.39-.77-27.44-12.6-27.44-26.93V91.01c0-14.89 13.06-27 29.09-27 19.28.1 122.46 7.38 192.12 38.29 11.26 5 18.64 15.75 18.66 27.84l.13 100.32V433zm272-45.29c0 14.33-12.05 26.16-27.45 26.93-42.92 2.18-129.46 9.3-191.91 32.51-1.8.67-3.62.98-5.39.98-8.11 0-15.26-6.52-15.26-15.13V230.46l.13-100.32c.01-12.09 7.4-22.84 18.66-27.84 69.66-30.91 172.84-38.19 192.12-38.29 16.03 0 29.09 12.11 29.09 27v296.7z" class=""></path></svg>
                </div>
                <div class="text-gray-500 text-center w-full">
                    No se encontraron resultados para la búsqueda
                </div>
            </div>

        </div>

    </AppLayout>
</template>
