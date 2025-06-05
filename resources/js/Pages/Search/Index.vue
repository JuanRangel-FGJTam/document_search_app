<script setup>
import { onMounted, ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3'
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
    searchType: String,
    months: Array,
    vehicleTypes: Array
});

const proccessing = ref(true);

const filtersForm = useForm({
    "credential": 0,
    "type": 0,
    "month": 6,
    "year" : 2025
});

onMounted(()=>{
    proccessing.value = true;

    router.reload({
        only: ['results'],
        onFinish:()=>{
            proccessing.value = false;
        }
    });
});

function submitFilters(e)
{
    filtersForm.get(route("filters"));
}

</script>

<template>
    <AppLayout title="Dashboard">
        <div class="p-6 flex flex-col max-w-screen-xl mx-auto border-t">

            <div class="row flex items-center justify-between bg-white pl-2 pr-4 shadow-sm rounded border">
                <div class="item mb-0">
                    <SearchInput :initial-search="props.search" :types="searchTypes" :searchType="searchType"/>
                </div>

                <div class="item -translate-y-2">
                    <form @submit.prevent="submitFilters" class="flex flex-wrap items-center justify-center gap-1 border rounded-xl border-gray-400 m-4 bg-slate-100 hover:bg-white transition-colors shadow px-1.5 pt-3 pb-0">
                        <div class="relative inline-block mb-0 pb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewbox="0 0 20 20" fill="currentColor" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path></svg>
                            <select v-model="filtersForm.credential" class="appearance-none w-full max-w-[15rem] text-gray-900 dark:text-white pl-10 py-2 pr-4 rounded-lg text-sm bg-transparent border-none focus:outline-none focus:ring-0 focus:border-none">
                                <option value="0">TODOS</option>
                                <option value="2">Con tarjeta de circulación proporcionada</option>
                                <option value="1">Sin tarjeta de circulación (extravío)</option>
                            </select>
                            <span class="absolute z-10 left-2.5 top-0 text-xs -translate-y-1.5 text-gray-800 dark:text-white text-opacity-60">
                                Cuenta con tarjeta de circulacion
                            </span>
                        </div>

                        <div class="relative inline-block mb-0 pb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewbox="0 0 20 20" fill="currentColor" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path></svg>
                            <select v-model="filtersForm.type" class="appearance-none w-full max-w-[12rem] text-gray-900 dark:text-white py-2 pl-10 pr-4 rounded-lg text-sm bg-transparent border-none focus:outline-none focus:ring-0 focus:border-none">
                                <option :value="0">TODOS</option>
                                <option v-for="(t, index) in vehicleTypes" :key="index" :value="t.id">{{t.name}}</option>
                            </select>
                            <span class="absolute z-10 left-2.5 top-0 text-xs hover:bg-white transition-colors -translate-y-1.5 text-gray-800 dark:text-white text-opacity-60">Tipo de vehiculo</span>
                        </div>

                        <div class="relative inline-block mb-0 pb-0">
                            <select v-model="filtersForm.month" name="month" id="month" class="appearance-none w-full max-w-[8rem] text-gray-900 py-2 rounded-lg bg-transparent border-none focus:outline-none focus:ring-0 focus:border-none text-sm">
                                <option v-for="m in Object.keys(months)" :key="m" :value="m"> {{ months[m]}}</option>
                            </select>
                            <span class="absolute z-10 left-2.5 top-0 text-xs hover:bg-white transition-colors -translate-y-1.5 text-gray-800 dark:text-white text-opacity-60">
                                Mes
                            </span>
                        </div>

                        <div class="relative inline-block mb-0 pb-0">
                            <select v-model="filtersForm.year" name="year" id="year" class="appearance-none w-full max-w-[14rem] text-gray-900 py-2 rounded-lg bg-transparent border-none focus:outline-none focus:ring-0 focus:border-none text-sm">
                                <option :value="2025">2025</option>
                            </select>
                            <span class="absolute z-10 left-2.5 top-0 text-xs -translate-y-1.5 text-gray-800 dark:text-white text-opacity-60">
                                Año
                            </span>
                        </div>

                        <button type="submit" class="border-none bg-[#3b4280] p-2 rounded-full text-white -translate-y-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0-14 0m18 11l-6-6"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <h1 class="flex items-center pt-4 gap-2 my-2 text-xl uppercase font-semibold text-gray-700 dark:text-gray-200">
                <span>Resultados</span>
                <AnimateSpin v-if="!results" class="w-4 h-4" />
                <span v-else class="font-normal">
                    : {{ results.length }} resultados
                </span>
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
