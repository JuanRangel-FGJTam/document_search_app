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
            <div class="col-span-1 text-sm"> {{ item.misplacement.documentNumber }}</div>
            <div class="col-span-2 text-xs"> {{ item.registerDate }}</div>
        </div>
        <CarPlate class="mx-auto" :plateNumber="item.plateNumber" />
        <div class="rounded uppercase">{{ item.fullName }}</div>

        <div class="flex flex-col text-sm text-left mt-2 pt-2 border-t">
            <div><strong>Marca:</strong> {{ item.vehicle.brandName }}</div>
            <div><strong>Sub Marca:</strong> {{ item.vehicle.subBrandName }}</div>
            <div><strong>Tipo:</strong> {{ item.vehicle.typeName }}</div>
            <div><strong>Placa:</strong> {{ item.vehicle.plateNumber }}</div>
            <div><strong>Numero de Serie:</strong> {{ item.vehicle.serieNumber }}</div>
            <div><strong>Dueño:</strong> {{ item.vehicle.owner }}</div>
        </div>

        <div class="flex flex-col text-sm text-left mt-2 pt-2 border-t">
            <div><strong>Nombre(s):</strong> {{ item.person.name }}</div>
            <div><strong>Apellido Paterno:</strong> {{ item.person.firstName }}</div>
            <div><strong>Apellido Materno:</strong> {{ item.person.lastName }}</div>
            <div><strong>CURP:</strong> {{ item.person.curp }}</div>
            <div><strong>Correo:</strong> {{ item.person.email }}</div>
            <div><strong>Fecha Nacimiento:</strong> {{ item.person.birthDate }}</div>
            <div><strong>Edad:</strong> {{ item.person.age }}</div>
            <div><strong>Genero:</strong> {{ item.person.genderName }}</div>
            <div><strong>Ocupacion:</strong> {{ item.person.occupationName }}</div>
        </div>

    </div>
</template>