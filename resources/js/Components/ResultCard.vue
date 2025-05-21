<script setup>
    import CarPlate from '@/Components/CarPlate.vue';

    const props = defineProps({
        item: {
            type: Object,
            required: true
        }
    });

</script>


<template>
    <div class="flex flex-col bg-white border shadow p-4 min-w-[320px] max-w-[350px] overflow-hidden">
        <div class="flex items-center justify-between gap-y-2 uppercase">
            <div class="col-span-1 text-lg"> {{ item.plateNumber }}</div>
            <div class="col-span-2 text-xs"> {{ item.registerDate }}</div>
        </div>
        <CarPlate class="mx-auto" :plateNumber="item.plateNumber" />
        <div class="rounded uppercase">{{ item.fullName }}</div>
        <pre class="text-xs text-left">
            {{ item.vehicle }}
        </pre>
        <pre class="text-xs text-left">
            {{ item.person }}
        </pre>
    </div>
</template>