<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchInput from '@/Components/SearchInputSm.vue';
import CarPlate from '@/Components/CarPlate.vue';
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
    }
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
                <SearchInput :initial-search="props.search" />
            </div>

            <h1 class="flex items-center gap-2 my-2 text-xl uppercase font-semibold text-gray-700 dark:text-gray-200">
                <span>Resultados</span>
                <AnimateSpin v-if="!results" class="w-4 h-4" />
            </h1>
            
            <div v-if="!results" class="my-2 grid grid-cols-4 gap-4">
                <ResultCardLoading />
                <ResultCardLoading />
                <ResultCardLoading />
            </div>

            <div v-else class="my-2 flex flex-wrap justify-around gap-4">
                <ResultCard v-for="(item, index) in results" :key="index" :item="item" />
            </div>

        </div>

    </AppLayout>
</template>
